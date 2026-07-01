"""
Amazon T-Level Hub — Backend API Server
========================================
One small Flask application that provides every server-side capability the
static front end actually calls:

    POST /api/application                    application.html form
    POST /api/contact                         contact modal (index.html,
                                               application.html, script.js)
    POST /api/feedback                        feedback modal
    GET  /api/chat/rooms                      chat.html room list
    GET  /api/chat/rooms/<room_id>/messages   chat.html message polling
    POST /api/chat/rooms/<room_id>/messages   chat.html message sending
    GET  /api/health                          simple uptime/status check

Design notes
------------
* Storage is a single local SQLite file (created automatically on first
  run) — no external database server or paid service required.
* The peer chatrooms are intentionally kept in memory rather than in
  SQLite: chat history here is a short-lived, best-effort scrollback (it
  is capped per room), not a record that needs to survive every restart.
  Applications and enquiries, which DO need to survive restarts, are the
  ones written to SQLite.
* Only Flask + Flask-CORS are required. See requirements.txt.

Run it with:
    pip install -r requirements.txt
    python backend_server.py

By default it listens on http://localhost:5000 (override with the HOST /
PORT environment variables). This matches the address the front end
already expects — see CHAT_API_BASE in chat.html and API_BASE in
script.js / application.html.
"""

from __future__ import annotations

import itertools
import logging
import os
import re
import sqlite3
import threading
import time
import uuid
from contextlib import contextmanager
from datetime import date, datetime

from flask import Flask, g, jsonify, request
from flask_cors import CORS

# ============================================================================
# Configuration
# ============================================================================

HOST = os.environ.get("HOST", "0.0.0.0")
PORT = int(os.environ.get("PORT", "5000"))
DB_PATH = os.environ.get("T_LEVEL_HUB_DB", os.path.join(os.path.dirname(__file__), "t_level_hub.db"))

MIN_APPLICANT_AGE = 16
MAX_APPLICANT_AGE = 19

# Simple anti-spam: minimum seconds between form submissions from the same IP.
FORM_RATE_LIMIT_SECONDS = 10
CHAT_RATE_LIMIT_SECONDS = 1.2

CHAT_ROOMS = {
    "general": "General Discussion",
    "digital": "Digital",
    "engineering": "Engineering & Manufacturing",
    "health-science": "Health & Science",
    "construction": "Construction",
    "business-admin": "Business & Administration",
    "education-childcare": "Education & Childcare",
    "legal-finance": "Legal & Finance",
}
MAX_CHAT_MESSAGE_LENGTH = 500
MAX_CHAT_HISTORY_PER_ROOM = 300
VALID_CHAT_ROLES = ("student", "teacher", "parent", "administrator")

EMAIL_RE = re.compile(r"^[^\s@]+@[^\s@]+\.[^\s@]+$")

logging.basicConfig(level=logging.INFO, format="%(asctime)s %(levelname)s %(message)s")
log = logging.getLogger("t_level_hub")

app = Flask(__name__)
CORS(app)


# ============================================================================
# Database
# ============================================================================

def init_db() -> None:
    """Create tables if they don't already exist. Safe to call every startup."""
    with get_db() as db:
        db.executescript(
            """
            CREATE TABLE IF NOT EXISTS applications (
                id                    INTEGER PRIMARY KEY AUTOINCREMENT,
                reference_number      TEXT UNIQUE NOT NULL,
                created_at            TEXT NOT NULL,
                first_name            TEXT NOT NULL,
                last_name             TEXT NOT NULL,
                date_of_birth         TEXT NOT NULL,
                nationality           TEXT,
                email                 TEXT NOT NULL,
                phone                 TEXT NOT NULL,
                country               TEXT,
                school                TEXT,
                year_of_study         TEXT,
                tlevel_course         TEXT NOT NULL,
                preferred_location    TEXT,
                preferred_start       TEXT,
                preferred_duration    TEXT,
                req_accommodation     INTEGER DEFAULT 0,
                req_transport         INTEGER DEFAULT 0,
                req_visa              INTEGER DEFAULT 0,
                req_language          INTEGER DEFAULT 0,
                heard_from            TEXT,
                motivation            TEXT,
                skills                TEXT,
                additional_needs      TEXT,
                status                TEXT NOT NULL DEFAULT 'submitted',
                submitted_ip          TEXT
            );

            CREATE TABLE IF NOT EXISTS enquiries (
                id           INTEGER PRIMARY KEY AUTOINCREMENT,
                kind         TEXT NOT NULL CHECK (kind IN ('contact', 'feedback')),
                created_at   TEXT NOT NULL,
                name         TEXT,
                email        TEXT NOT NULL,
                subject      TEXT,
                rating       TEXT,
                message      TEXT NOT NULL,
                submitted_ip TEXT
            );
            """
        )


@contextmanager
def get_db():
    """Yield a SQLite connection, committing on success and rolling back on error."""
    db = sqlite3.connect(DB_PATH)
    db.row_factory = sqlite3.Row
    db.execute("PRAGMA foreign_keys = ON")
    try:
        yield db
        db.commit()
    except Exception:
        db.rollback()
        raise
    finally:
        db.close()


# ============================================================================
# Shared helpers
# ============================================================================

def is_valid_email(value: str) -> bool:
    return bool(value) and bool(EMAIL_RE.match(value.strip()))


def calculate_age(dob_str: str) -> int | None:
    """Parse a YYYY-MM-DD date-of-birth string and return whole years of age."""
    try:
        dob = datetime.strptime(dob_str, "%Y-%m-%d").date()
    except (ValueError, TypeError):
        return None
    today = date.today()
    years = today.year - dob.year - ((today.month, today.day) < (dob.month, dob.day))
    return years


def generate_reference_number() -> str:
    """Generate a human-shareable, collision-checked application reference."""
    year = datetime.now().year
    with get_db() as db:
        for _ in range(10):
            candidate = f"TL-{year}-{uuid.uuid4().hex[:6].upper()}"
            exists = db.execute(
                "SELECT 1 FROM applications WHERE reference_number = ?", (candidate,)
            ).fetchone()
            if not exists:
                return candidate
    # Astronomically unlikely fallback, but keep it deterministic-ish.
    return f"TL-{year}-{uuid.uuid4().hex[:10].upper()}"


def error_response(message: str, validation_errors: list[str] | None = None, status: int = 400):
    payload = {"error": True, "message": message}
    if validation_errors:
        payload["validation_errors"] = validation_errors
    return jsonify(payload), status


# ----- very small per-IP rate limiter, shared by all POST endpoints below --

_rate_lock = threading.Lock()
_last_submission_by_key: dict[str, float] = {}


def rate_limited(key: str, min_interval_seconds: float) -> bool:
    """Return True (and record the attempt) if `key` is submitting too fast."""
    now = time.time()
    with _rate_lock:
        last = _last_submission_by_key.get(key, 0.0)
        if now - last < min_interval_seconds:
            return True
        _last_submission_by_key[key] = now
    return False


def client_ip() -> str:
    # Respect a reverse proxy's forwarded header if present, else fall back.
    forwarded = request.headers.get("X-Forwarded-For", "")
    return forwarded.split(",")[0].strip() if forwarded else (request.remote_addr or "unknown")


# ============================================================================
# Health
# ============================================================================

@app.route("/api/health", methods=["GET"])
def health():
    return jsonify({"status": "ok", "time": datetime.utcnow().isoformat() + "Z"})


# ============================================================================
# Applications  (application.html)
# ============================================================================

REQUIRED_APPLICATION_FIELDS = [
    "first_name",
    "last_name",
    "date_of_birth",
    "email",
    "phone",
    "tlevel_course",
]


def validate_application(data: dict) -> list[str]:
    errors = []

    for field in REQUIRED_APPLICATION_FIELDS:
        if not str(data.get(field, "")).strip():
            errors.append(f"'{field.replace('_', ' ')}' is required.")

    email = str(data.get("email", "")).strip()
    if email and not is_valid_email(email):
        errors.append("Please enter a valid email address.")

    dob = str(data.get("date_of_birth", "")).strip()
    if dob:
        age = calculate_age(dob)
        if age is None:
            errors.append("Please enter a valid date of birth (YYYY-MM-DD).")
        elif not (MIN_APPLICANT_AGE <= age <= MAX_APPLICANT_AGE):
            errors.append(
                f"Applicants must be between {MIN_APPLICANT_AGE} and {MAX_APPLICANT_AGE} "
                f"years old to apply for a T-Level placement (you are {age})."
            )

    for flag in ("declarations_accurate", "declarations_privacy", "declarations_contact"):
        if not data.get(flag):
            errors.append("You must accept all three declarations before submitting.")
            break

    return errors


@app.route("/api/application", methods=["POST"])
def submit_application():
    if rate_limited("application:" + client_ip(), FORM_RATE_LIMIT_SECONDS):
        return error_response("You're submitting too quickly. Please wait a moment and try again.", status=429)

    data = request.get_json(silent=True) or {}
    validation_errors = validate_application(data)
    if validation_errors:
        return error_response("Please correct the errors below.", validation_errors)

    reference_number = generate_reference_number()

    with get_db() as db:
        db.execute(
            """
            INSERT INTO applications (
                reference_number, created_at, first_name, last_name, date_of_birth,
                nationality, email, phone, country, school, year_of_study,
                tlevel_course, preferred_location, preferred_start, preferred_duration,
                req_accommodation, req_transport, req_visa, req_language,
                heard_from, motivation, skills, additional_needs, status, submitted_ip
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            """,
            (
                reference_number,
                datetime.utcnow().isoformat() + "Z",
                str(data.get("first_name", "")).strip(),
                str(data.get("last_name", "")).strip(),
                str(data.get("date_of_birth", "")).strip(),
                str(data.get("nationality", "")).strip(),
                str(data.get("email", "")).strip(),
                str(data.get("phone", "")).strip(),
                str(data.get("country", "")).strip(),
                str(data.get("school", "")).strip(),
                str(data.get("year_of_study", "")).strip(),
                str(data.get("tlevel_course", "")).strip(),
                str(data.get("preferred_location", "")).strip(),
                str(data.get("preferred_start", "")).strip(),
                str(data.get("preferred_duration", "")).strip(),
                int(bool(data.get("req_accommodation"))),
                int(bool(data.get("req_transport"))),
                int(bool(data.get("req_visa"))),
                int(bool(data.get("req_language"))),
                str(data.get("heard_from", "")).strip(),
                str(data.get("motivation", "")).strip(),
                str(data.get("skills", "")).strip(),
                str(data.get("additional_needs", "")).strip(),
                "submitted",
                client_ip(),
            ),
        )

    log.info("New application %s from %s", reference_number, data.get("email"))
    return jsonify({"application": {"ref_number": reference_number}}), 201


# ============================================================================
# Contact & feedback  (index.html / application.html / script.js)
# ============================================================================

@app.route("/api/contact", methods=["POST"])
def submit_contact():
    if rate_limited("contact:" + client_ip(), FORM_RATE_LIMIT_SECONDS):
        return error_response("You're submitting too quickly. Please wait a moment and try again.", status=429)

    data = request.get_json(silent=True) or {}
    email = str(data.get("email", "")).strip()
    subject = str(data.get("subject", "")).strip()
    message = str(data.get("message", "")).strip()

    errors = []
    if not is_valid_email(email):
        errors.append("Please enter a valid email address.")
    if not subject:
        errors.append("Please enter a subject.")
    if not message:
        errors.append("Please enter a message.")
    if errors:
        return error_response("Please correct the errors below.", errors)

    with get_db() as db:
        db.execute(
            "INSERT INTO enquiries (kind, created_at, name, email, subject, message, submitted_ip) "
            "VALUES ('contact', ?, ?, ?, ?, ?, ?)",
            (datetime.utcnow().isoformat() + "Z", str(data.get("name", "")).strip(), email, subject, message, client_ip()),
        )

    log.info("New contact enquiry from %s", email)
    return jsonify({"ok": True}), 201


@app.route("/api/feedback", methods=["POST"])
def submit_feedback():
    if rate_limited("feedback:" + client_ip(), FORM_RATE_LIMIT_SECONDS):
        return error_response("You're submitting too quickly. Please wait a moment and try again.", status=429)

    data = request.get_json(silent=True) or {}
    email = str(data.get("email", "")).strip()
    rating = str(data.get("rating", "")).strip()
    message = str(data.get("feedback", data.get("message", ""))).strip()

    errors = []
    if not is_valid_email(email):
        errors.append("Please enter a valid email address.")
    if not rating:
        errors.append("Please choose a rating.")
    if not message:
        errors.append("Please enter your feedback.")
    if errors:
        return error_response("Please correct the errors below.", errors)

    with get_db() as db:
        db.execute(
            "INSERT INTO enquiries (kind, created_at, name, email, rating, message, submitted_ip) "
            "VALUES ('feedback', ?, ?, ?, ?, ?, ?)",
            (datetime.utcnow().isoformat() + "Z", str(data.get("name", "")).strip(), email, rating, message, client_ip()),
        )

    log.info("New feedback from %s", email)
    return jsonify({"ok": True}), 201


# ============================================================================
# Peer chatrooms  (chat.html)
# ============================================================================
# In-memory on purpose — see module docstring. Guarded by a lock because
# Flask's dev server (and most WSGI servers) can process requests on
# multiple threads at once.

_chat_lock = threading.Lock()
_chat_messages: dict[str, list[dict]] = {room: [] for room in CHAT_ROOMS}
_chat_id_counter = itertools.count(1)


def sanitize_chat_text(text: str) -> str:
    text = text.strip()[:MAX_CHAT_MESSAGE_LENGTH]
    return (
        text.replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;").replace('"', "&quot;")
    )


def sanitize_chat_name(name: str | None) -> str:
    name = (name or "Guest").strip()[:40]
    name = re.sub(r"[<>]", "", name)
    return name or "Guest"


@app.route("/api/chat/rooms", methods=["GET"])
def list_chat_rooms():
    return jsonify({"rooms": [{"id": rid, "name": rname} for rid, rname in CHAT_ROOMS.items()]})


@app.route("/api/chat/rooms/<room_id>/messages", methods=["GET"])
def get_chat_messages(room_id):
    if room_id not in CHAT_ROOMS:
        return error_response("Unknown room.", status=404)

    since = request.args.get("since", default=0, type=int)
    with _chat_lock:
        messages = [m for m in _chat_messages[room_id] if m["id"] > since]
    return jsonify({"room": room_id, "messages": messages})


@app.route("/api/chat/rooms/<room_id>/messages", methods=["POST"])
def post_chat_message(room_id):
    if room_id not in CHAT_ROOMS:
        return error_response("Unknown room.", status=404)

    data = request.get_json(silent=True) or {}
    user = sanitize_chat_name(data.get("user"))
    role = data.get("role") if data.get("role") in VALID_CHAT_ROLES else "student"
    text = str(data.get("text", ""))

    if not text.strip():
        return error_response("Message text is required.")

    if rate_limited("chat:" + user, CHAT_RATE_LIMIT_SECONDS):
        return error_response("You're sending messages too quickly. Please slow down.", status=429)

    message = {
        "id": next(_chat_id_counter),
        "user": user,
        "role": role,
        "text": sanitize_chat_text(text),
        "ts": time.time(),
    }

    with _chat_lock:
        _chat_messages[room_id].append(message)
        if len(_chat_messages[room_id]) > MAX_CHAT_HISTORY_PER_ROOM:
            _chat_messages[room_id] = _chat_messages[room_id][-MAX_CHAT_HISTORY_PER_ROOM:]

    return jsonify({"message": message}), 201


# ============================================================================
# Error handlers
# ============================================================================

@app.errorhandler(404)
def not_found(_e):
    return error_response("Not found.", status=404)


@app.errorhandler(500)
def server_error(e):
    log.exception("Unhandled server error")
    return error_response("Internal server error.", status=500)


# ============================================================================
# Entry point
# ============================================================================

if __name__ == "__main__":
    init_db()
    log.info("Amazon T-Level Hub backend starting on http://%s:%s", HOST, PORT)
    log.info("Database: %s", DB_PATH)
    app.run(host=HOST, port=PORT, debug=False)

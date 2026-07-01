# Amazon T-Level Hub — Backend Setup

This project actually has **two** small backend pieces, doing two
different jobs. You need both running for the full site to work.

## 1. The PHP backend (`backend/` folder) — the main one

Handles: the application form, contact form, feedback form, course data,
and the admin dashboard/login. Stores everything in a local SQLite file.

### Where the files go

```
your-project-folder/
├── index.html, login.html, chat.html, application.html, resources.html
├── style.css, script.js, icons.js, icons.svg
│
└── backend/
    ├── index.php          ← admin dashboard
    ├── install.php        ← run once, then delete
    ├── .htaccess          ← rename from _htaccess (see below)
    │
    ├── config/
    │   ├── database.php
    │   └── app.php
    │
    └── api/
        ├── auth.php
        ├── courses.php
        ├── submit_application.php
        ├── submit_contact.php
        ├── submit_feedback.php
        ├── admin_applications.php
        ├── admin_enquiries.php
        └── admin_feedback.php
```

A `backend/data/` folder (holding the SQLite database and log file) is
created automatically the first time you run `install.php` — you don't
need to make it yourself.

**Rename `_htaccess` to `.htaccess`** once it's inside `backend/` — files
can't be uploaded starting with a dot, so it came through with an
underscore instead.

### Running it

You need a PHP environment with SQLite/PDO support (this is standard —
XAMPP, MAMP, most shared hosting, or plain `php` on your machine all
have it). From the project root:

```bash
php -S localhost:8000
```

Then visit `http://localhost:8000/backend/install.php` once, in your
browser, to create the database and a default admin account (it will
print a generated password — save it, then delete `install.php`).

The site's forms already call the right relative paths
(`backend/api/submit_contact.php`, etc.) — no front-end changes needed
as long as `backend/` sits next to `index.html` like above.

## 2. The Python chat server (`chatroom_server.py`) — for peer chat only

The PHP backend above does **not** implement the peer chatrooms used by
`chat.html` (general chat + one room per T-Level pathway). That one
feature is handled by a separate, tiny Python server.

```bash
pip install -r requirements.txt
python chatroom_server.py
```

It listens on `http://localhost:5000`, which is what `chat.html`
already points at (`CHAT_API_BASE`). Nothing else to configure.

## `chatbot_server.py` — optional, not required

An experimental alternative to the chatbot widget on `index.html`. The
live chatbot already works fully offline in `script.js` — this file
uses a real sentence-embedding model instead of keyword matching, for
anyone who wants to try that approach. It needs extra dependencies (see
`requirements-ai.txt`, which pulls in PyTorch — a large download) and
isn't wired up to the site by default.

## Summary: what to run

| Need | Run |
|---|---|
| Application/contact/feedback forms, admin panel | PHP: `php -S localhost:8000` (with `backend/` set up as above) |
| Peer chatrooms in `chat.html` | Python: `python chatroom_server.py` |
| Everything else (browsing pages, the offline chatbot) | Nothing — it's all static HTML/CSS/JS |

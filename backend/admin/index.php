<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel – Amazon T-Level Hub</title>
<style>
:root {
    --bg: #0f1923;
    --surface: #1a2738;
    --border: rgba(255,255,255,0.08);
    --text: #e0e8f0;
    --muted: #7a8a9a;
    --accent: #ff9900;
    --success: #4caf50;
    --danger: #e53935;
    --warning: #ff9800;
}
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
html { font-size: 14px; }
body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    display: flex;
}
/* Sidebar */
.sidebar {
    width: 240px;
    background: var(--surface);
    border-right: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    z-index: 100;
}
.sidebar-header {
    padding: 20px;
    border-bottom: 1px solid var(--border);
}
.sidebar-header h1 { font-size: 16px; color: var(--accent); }
.sidebar-header p { font-size: 11px; color: var(--muted); margin-top: 4px; }
.sidebar-nav { flex: 1; padding: 12px 0; overflow-y: auto; }
.sidebar-nav a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 20px;
    color: var(--muted);
    text-decoration: none;
    font-size: 14px;
    transition: .2s;
    border-left: 3px solid transparent;
}
.sidebar-nav a:hover, .sidebar-nav a.active {
    background: rgba(255,153,0,0.06);
    color: var(--text);
    border-left-color: var(--accent);
}
.sidebar-footer {
    padding: 16px 20px;
    border-top: 1px solid var(--border);
    font-size: 12px;
    color: var(--muted);
}
/* Main */
.main {
    margin-left: 240px;
    flex: 1;
    padding: 30px 36px;
    min-height: 100vh;
}
.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}
.top-bar h2 { font-size: 22px; font-weight: 600; }
.user-info {
    display: flex;
    align-items: center;
    gap: 14px;
}
.user-info span { color: var(--muted); font-size: 13px; }
.logout-btn {
    background: transparent;
    border: 1px solid rgba(255,255,255,0.15);
    color: var(--muted);
    padding: 7px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    transition: .2s;
}
.logout-btn:hover { border-color: var(--danger); color: var(--danger); }
/* Stats */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 30px;
}
.stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 20px;
}
.stat-card .stat-value { font-size: 28px; font-weight: 700; color: var(--accent); }
.stat-card .stat-label { font-size: 12px; color: var(--muted); margin-top: 4px; }
/* Login screen */
.login-screen {
    display: none;
    position: fixed;
    inset: 0;
    background: var(--bg);
    z-index: 200;
    justify-content: center;
    align-items: center;
}
.login-screen.active { display: flex; }
.login-box {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 40px;
    width: 100%;
    max-width: 380px;
}
.login-box h2 { font-size: 20px; margin-bottom: 6px; }
.login-box .subtitle { color: var(--muted); font-size: 13px; margin-bottom: 24px; }
.login-box .field { margin-bottom: 16px; }
.login-box label { display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px; color: var(--muted); }
.login-box input {
    width: 100%;
    padding: 11px 14px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--bg);
    color: var(--text);
    font-size: 14px;
    outline: none;
    transition: .2s;
}
.login-box input:focus { border-color: var(--accent); }
.login-btn {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    border: none;
    background: var(--accent);
    color: var(--bg);
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: .2s;
    margin-top: 8px;
}
.login-btn:hover { opacity: 0.9; }
.login-error { color: var(--danger); font-size: 12px; margin-top: 10px; display: none; }
/* Table */
.table-container {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 10px;
    overflow: hidden;
}
.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
    flex-wrap: wrap;
    gap: 10px;
}
.table-header h3 { font-size: 16px; }
.search-input {
    padding: 8px 14px;
    border-radius: 6px;
    border: 1px solid var(--border);
    background: var(--bg);
    color: var(--text);
    font-size: 13px;
    width: 220px;
    outline: none;
}
.search-input:focus { border-color: var(--accent); }
table { width: 100%; border-collapse: collapse; }
th { text-align: left; padding: 12px 16px; font-size: 11px; text-transform: uppercase; letter-spacing: .5px; color: var(--muted); border-bottom: 1px solid var(--border); }
td { padding: 12px 16px; font-size: 13px; border-bottom: 1px solid rgba(255,255,255,0.03); }
tr:hover td { background: rgba(255,255,255,0.02); }
.status-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .3px;
}
.status-unread { background: rgba(255,152,0,0.15); color: var(--warning); }
.status-read { background: rgba(33,150,243,0.15); color: #2196f3; }
.status-replied { background: rgba(76,175,80,0.15); color: var(--success); }
.status-archived { background: rgba(158,158,158,0.15); color: #9e9e9e; }
.status-new { background: rgba(255,152,0,0.15); color: var(--warning); }
.status-reviewed { background: rgba(76,175,80,0.15); color: var(--success); }
.status-received { background: rgba(255,152,0,0.15); color: var(--warning); }
.status-reviewing { background: rgba(33,150,243,0.15); color: #2196f3; }
.status-shortlisted { background: rgba(156,39,176,0.15); color: #9c27b0; }
.status-interviewed { background: rgba(0,188,212,0.15); color: #00bcd4; }
.status-offered { background: rgba(76,175,80,0.15); color: var(--success); }
.status-accepted { background: rgba(76,175,80,0.15); color: var(--success); }
.status-rejected { background: rgba(229,57,53,0.15); color: var(--danger); }
.status-withdrawn { background: rgba(158,158,158,0.15); color: #9e9e9e; }
.action-btn {
    background: transparent;
    border: 1px solid var(--border);
    color: var(--muted);
    padding: 4px 10px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    transition: .2s;
}
.action-btn:hover { border-color: var(--accent); color: var(--accent); }
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    padding: 16px;
}
.pagination button {
    background: transparent;
    border: 1px solid var(--border);
    color: var(--muted);
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
}
.pagination button:hover { border-color: var(--accent); color: var(--accent); }
.pagination button:disabled { opacity: 0.3; cursor: default; }
.pagination .page-info { color: var(--muted); font-size: 12px; }
/* Modal */
.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.6);
    z-index: 300;
    justify-content: center;
    align-items: center;
    padding: 20px;
}
.modal-overlay.active { display: flex; }
.modal-box {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    width: 100%;
    max-width: 600px;
    max-height: 80vh;
    overflow-y: auto;
    padding: 28px;
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.modal-header h3 { font-size: 18px; }
.modal-close {
    background: none;
    border: none;
    color: var(--muted);
    font-size: 24px;
    cursor: pointer;
}
.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.detail-item { }
.detail-item label { font-size: 11px; color: var(--muted); display: block; margin-bottom: 2px; }
.detail-item span { font-size: 13px; }
.detail-item.full { grid-column: 1 / -1; }
.modal-actions { margin-top: 20px; display: flex; gap: 10px; }
.modal-actions select {
    padding: 8px 12px;
    border-radius: 6px;
    border: 1px solid var(--border);
    background: var(--bg);
    color: var(--text);
    font-size: 13px;
}
.modal-actions textarea {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid var(--border);
    background: var(--bg);
    color: var(--text);
    font-size: 13px;
    resize: vertical;
    min-height: 80px;
    margin-top: 10px;
}
.save-btn {
    padding: 8px 20px;
    border-radius: 6px;
    border: none;
    background: var(--accent);
    color: var(--bg);
    font-weight: 600;
    cursor: pointer;
}
.delete-btn {
    padding: 8px 20px;
    border-radius: 6px;
    border: 1px solid var(--danger);
    background: transparent;
    color: var(--danger);
    cursor: pointer;
}
/* Loading */
.loading { text-align: center; padding: 40px; color: var(--muted); }
</style>
</head>
<body>

<!-- LOGIN SCREEN -->
<div class="login-screen active" id="loginScreen">
    <div class="login-box">
        <h2>Admin Login</h2>
        <p class="subtitle">Amazon T-Level Hub – Administration Panel</p>
        <div class="field">
            <label>Username</label>
            <input type="text" id="loginUsername" placeholder="Enter username" autocomplete="username">
        </div>
        <div class="field">
            <label>Password</label>
            <input type="password" id="loginPassword" placeholder="Enter password" autocomplete="current-password">
        </div>
        <button class="login-btn" id="loginBtn">Sign In</button>
        <div class="login-error" id="loginError">Invalid username or password.</div>
        <div class="login-error" id="loginLockout" style="display:none;">Too many attempts. Try again later.</div>
    </div>
</div>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-header">
        <h1>Amazon T-Level Hub</h1>
        <p>Administration Panel</p>
    </div>
    <nav class="sidebar-nav">
        <a href="#" class="active" onclick="showSection('dashboard', this)">📊 Dashboard</a>
        <a href="#" onclick="showSection('applications', this)">📋 Applications</a>
        <a href="#" onclick="showSection('enquiries', this)">✉️ Enquiries</a>
        <a href="#" onclick="showSection('feedback', this)">⭐ Feedback</a>
        <a href="#" onclick="showSection('stats', this)">📈 Reports</a>
        <a href="#" onclick="showSection('settings', this)">⚙️ Settings</a>
    </nav>
    <div class="sidebar-footer">
        <span id="sidebarUser">Not logged in</span>
    </div>
</aside>

<!-- MAIN -->
<div class="main" id="mainContent">
    <div class="top-bar">
        <h2 id="pageTitle">Dashboard</h2>
        <div class="user-info">
            <span id="userDisplay">Not logged in</span>
            <button class="logout-btn" id="logoutBtn" onclick="logout()">Logout</button>
        </div>
    </div>

    <!-- Dashboard Section -->
    <div id="section-dashboard">
        <div class="stats-grid" id="statsGrid">
            <div class="stat-card"><div class="stat-value" id="statTotal">-</div><div class="stat-label">Total Applications</div></div>
            <div class="stat-card"><div class="stat-value" id="statPending">-</div><div class="stat-label">Pending Review</div></div>
            <div class="stat-card"><div class="stat-value" id="statShortlisted">-</div><div class="stat-label">Shortlisted</div></div>
            <div class="stat-card"><div class="stat-value" id="statOffered">-</div><div class="stat-label">Offers Made</div></div>
            <div class="stat-card"><div class="stat-value" id="statEnquiries">-</div><div class="stat-label">Unread Enquiries</div></div>
            <div class="stat-card"><div class="stat-value" id="statFeedback">-</div><div class="stat-label">Feedback Received</div></div>
            <div class="stat-card"><div class="stat-value" id="statAvgRating">-</div><div class="stat-label">Average Rating</div></div>
        </div>
    </div>

    <!-- Applications Section -->
    <div id="section-applications" style="display:none;">
        <div class="table-container">
            <div class="table-header">
                <h3>All Applications</h3>
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <select id="statusFilter" onchange="loadApplications()" style="padding:8px 12px;border-radius:6px;border:1px solid var(--border);background:var(--bg);color:var(--text);font-size:13px;">
                        <option value="">All Statuses</option>
                        <option value="received">Received</option>
                        <option value="reviewing">Reviewing</option>
                        <option value="shortlisted">Shortlisted</option>
                        <option value="interviewed">Interviewed</option>
                        <option value="offered">Offered</option>
                        <option value="accepted">Accepted</option>
                        <option value="rejected">Rejected</option>
                        <option value="withdrawn">Withdrawn</option>
                    </select>
                    <input type="text" class="search-input" id="searchInput" placeholder="Search name, email, ref..." onkeyup="loadApplications()">
                </div>
            </div>
            <div id="applicationsTable">
                <div class="loading">Loading applications...</div>
            </div>
            <div class="pagination" id="pagination"></div>
        </div>
    </div>

    <!-- Enquiries Section -->
    <div id="section-enquiries" style="display:none;">
        <div class="table-container">
            <div class="table-header">
                <h3>Contact Enquiries</h3>
            </div>
            <div id="enquiriesTable">
                <div class="loading">Loading enquiries...</div>
            </div>
        </div>
    </div>

    <!-- Feedback Section -->
    <div id="section-feedback" style="display:none;">
        <div class="table-container">
            <div class="table-header">
                <h3>User Feedback</h3>
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <select id="feedbackRatingFilter" onchange="loadFeedback()" style="padding:8px 12px;border-radius:6px;border:1px solid var(--border);background:var(--bg);color:var(--text);font-size:13px;">
                        <option value="">All Ratings</option>
                        <option value="5">5 stars</option>
                        <option value="4">4 stars</option>
                        <option value="3">3 stars</option>
                        <option value="2">2 stars</option>
                        <option value="1">1 star</option>
                    </select>
                    <select id="feedbackStatusFilter" onchange="loadFeedback()" style="padding:8px 12px;border-radius:6px;border:1px solid var(--border);background:var(--bg);color:var(--text);font-size:13px;">
                        <option value="">All Statuses</option>
                        <option value="new">New</option>
                        <option value="reviewed">Reviewed</option>
                        <option value="archived">Archived</option>
                    </select>
                    <input type="text" class="search-input" id="feedbackSearchInput" placeholder="Search email, text..." onkeyup="loadFeedback()">
                </div>
            </div>
            <div id="feedbackTable">
                <div class="loading">Loading feedback...</div>
            </div>
            <div class="pagination" id="feedbackPagination"></div>
        </div>
    </div>

    <!-- Reports Section -->
    <div id="section-stats" style="display:none;">
        <div class="stats-grid">
            <div class="stat-card"><div class="stat-value" id="reportTotal">-</div><div class="stat-label">Total Applications</div></div>
            <div class="stat-card"><div class="stat-value" id="reportDigital">-</div><div class="stat-label">Digital Courses</div></div>
            <div class="stat-card"><div class="stat-value" id="reportHealth">-</div><div class="stat-label">Health & Science</div></div>
            <div class="stat-card"><div class="stat-value" id="reportConstruction">-</div><div class="stat-label">Construction</div></div>
            <div class="stat-card"><div class="stat-value" id="reportFinance">-</div><div class="stat-label">Legal & Finance</div></div>
            <div class="stat-card"><div class="stat-value" id="reportEducation">-</div><div class="stat-label">Education</div></div>
        </div>
        <div style="margin-top:20px;">
            <button class="save-btn" onclick="exportReport()" style="background:var(--success);">📥 Export Report (JSON)</button>
        </div>
    </div>

    <!-- Settings Section -->
    <div id="section-settings" style="display:none;">
        <div class="table-container" style="padding:24px;">
            <h3 style="margin-bottom:16px;">Application Settings</h3>
            <div style="color:var(--muted);font-size:13px;line-height:1.8;">
                <p><strong>App Name:</strong> Amazon T-Level Hub</p>
                <p><strong>Version:</strong> 1.0.0</p>
                <p><strong>Support Email:</strong> tlevel@amazon.co.uk</p>
                <p><strong>Support Phone:</strong> 0800 000 1234</p>
                <p><strong>Token Expiry:</strong> 24 hours</p>
                <p><strong>Max Login Attempts:</strong> 5 before 15 min lockout</p>
                <p><strong>Data Retention:</strong> Applications kept for 2 years (GDPR compliant)</p>
                <p><strong>Database:</strong> SQLite (WAL mode)</p>
            </div>
        </div>
    </div>
</div>

<!-- DETAIL MODAL -->
<div class="modal-overlay" id="detailModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalTitle">Application Details</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div id="modalBody"></div>
    </div>
</div>

<script>
// ===== STATE =====
let authToken = localStorage.getItem('admin_token') || '';
let currentUser = null;
let currentPage = 1;
const API_BASE = '';

// ===== INIT =====
document.addEventListener('DOMContentLoaded', function() {
    if (authToken) {
        verifyToken();
    }
    // Login on Enter key
    document.getElementById('loginPassword').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') login();
    });
    document.getElementById('loginUsername').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') document.getElementById('loginPassword').focus();
    });
    document.getElementById('loginBtn').addEventListener('click', login);
});

// ===== AUTH =====
async function login() {
    const username = document.getElementById('loginUsername').value.trim();
    const password = document.getElementById('loginPassword').value;
    if (!username || !password) return;

    document.getElementById('loginBtn').textContent = 'Signing in...';
    document.getElementById('loginBtn').disabled = true;
    document.getElementById('loginError').style.display = 'none';
    document.getElementById('loginLockout').style.display = 'none';

    try {
        const res = await fetch(API_BASE + 'api/auth.php?action=login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        });
        const data = await res.json();
        
        if (data.error) {
            if (res.status === 429) {
                document.getElementById('loginLockout').style.display = 'block';
            } else {
                document.getElementById('loginError').style.display = 'block';
            }
            return;
        }

        authToken = data.token;
        currentUser = data.user;
        localStorage.setItem('admin_token', authToken);
        showDashboard();
    } catch (err) {
        document.getElementById('loginError').textContent = 'Connection error. Please try again.';
        document.getElementById('loginError').style.display = 'block';
    } finally {
        document.getElementById('loginBtn').textContent = 'Sign In';
        document.getElementById('loginBtn').disabled = false;
    }
}

async function verifyToken() {
    try {
        const res = await fetch(API_BASE + 'api/auth.php?action=verify', {
            headers: { 'Authorization': 'Bearer ' + authToken }
        });
        const data = await res.json();
        if (!data.error && data.valid) {
            currentUser = data.user;
            showDashboard();
        } else {
            localStorage.removeItem('admin_token');
            authToken = '';
        }
    } catch (err) {
        // Offline - still show login
    }
}

async function logout() {
    try {
        await fetch(API_BASE + 'api/auth.php?action=logout', {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + authToken }
        });
    } catch (err) {}
    localStorage.removeItem('admin_token');
    authToken = '';
    currentUser = null;
    document.getElementById('loginScreen').classList.add('active');
    document.getElementById('loginUsername').value = '';
    document.getElementById('loginPassword').value = '';
}

function showDashboard() {
    document.getElementById('loginScreen').classList.remove('active');
    document.getElementById('sidebarUser').textContent = currentUser ? currentUser.display_name : 'Admin';
    document.getElementById('userDisplay').textContent = currentUser ? currentUser.display_name + ' (' + currentUser.role + ')' : 'Admin';
    loadDashboardStats();
}

function authHeaders() {
    return { 'Authorization': 'Bearer ' + authToken, 'Content-Type': 'application/json' };
}

// ===== NAVIGATION =====
function showSection(section, el) {
    document.querySelectorAll('.sidebar-nav a').forEach(a => a.classList.remove('active'));
    if (el) el.classList.add('active');
    
    document.querySelectorAll('[id^="section-"]').forEach(s => s.style.display = 'none');
    
    const titles = {
        dashboard: 'Dashboard',
        applications: 'Applications',
        enquiries: 'Contact Enquiries',
        feedback: 'User Feedback',
        stats: 'Reports & Analytics',
        settings: 'System Settings'
    };
    document.getElementById('pageTitle').textContent = titles[section] || section;
    document.getElementById('section-' + section).style.display = 'block';
    
    if (section === 'applications') loadApplications();
    if (section === 'enquiries') loadEnquiries();
    if (section === 'feedback') loadFeedback();
    if (section === 'stats') loadReportStats();
    if (section === 'dashboard') loadDashboardStats();
}

// ===== DASHBOARD =====
async function loadDashboardStats() {
    try {
        const [appRes, enqRes, fbRes] = await Promise.all([
            fetch(API_BASE + 'api/admin_applications.php', { headers: authHeaders() }),
            fetch(API_BASE + 'api/admin_enquiries.php?status=unread', { headers: authHeaders() }),
            fetch(API_BASE + 'api/admin_feedback.php', { headers: authHeaders() })
        ]);
        const appData = await appRes.json();
        const enqData = await enqRes.json();
        const fbData = await fbRes.json();

        if (!fbData.error && fbData.pagination) {
            document.getElementById('statFeedback').textContent = fbData.pagination.total;
            document.getElementById('statAvgRating').textContent = fbData.average_rating !== null ? fbData.average_rating + ' / 5' : '-';
        }

        if (!appData.error && appData.pagination) {
            document.getElementById('statTotal').textContent = appData.pagination.total;
            
            // Count by status
            const apps = appData.applications || [];
            const received = apps.filter(a => a.status === 'received').length;
            const shortlisted = apps.filter(a => a.status === 'shortlisted' || a.status === 'interviewed').length;
            const offered = apps.filter(a => a.status === 'offered' || a.status === 'accepted').length;
            
            document.getElementById('statPending').textContent = received;
            document.getElementById('statShortlisted').textContent = shortlisted;
            document.getElementById('statOffered').textContent = offered;
            document.getElementById('statEnquiries').textContent = enqData.pagination ? enqData.pagination.total : '-';
        }
    } catch (err) {
        console.error('Dashboard load error:', err);
    }
}

// ===== APPLICATIONS =====
let currentAppPage = 1;

async function loadApplications() {
    const status = document.getElementById('statusFilter').value;
    const search = document.getElementById('searchInput').value.trim();
    
    let url = API_BASE + 'api/admin_applications.php?page=' + currentAppPage + '&limit=15&sort_by=submitted_at&sort_order=DESC';
    if (status) url += '&status=' + encodeURIComponent(status);
    if (search) url += '&search=' + encodeURIComponent(search);

    try {
        const res = await fetch(url, { headers: authHeaders() });
        const data = await res.json();

        if (data.error) {
            document.getElementById('applicationsTable').innerHTML = '<div class="loading" style="color:var(--danger)">Error loading applications.</div>';
            return;
        }

        const apps = data.applications || [];
        const pag = data.pagination || {};

        if (apps.length === 0) {
            document.getElementById('applicationsTable').innerHTML = '<div class="loading">No applications found.</div>';
            document.getElementById('pagination').innerHTML = '';
            return;
        }

        let html = `<table><thead><tr>
            <th>Ref #</th><th>Name</th><th>Email</th><th>Course</th><th>Location</th><th>Status</th><th>Date</th><th></th>
        </tr></thead><tbody>`;

        apps.forEach(app => {
            const statusClass = 'status-' + app.status;
            const date = new Date(app.submitted_at).toLocaleDateString();
            html += `<tr>
                <td style="font-family:monospace;font-size:12px;">${app.ref_number}</td>
                <td>${app.first_name} ${app.last_name}</td>
                <td style="font-size:12px;">${app.email}</td>
                <td>${app.tlevel_course}</td>
                <td>${app.preferred_location}</td>
                <td><span class="status-badge ${statusClass}">${app.status}</span></td>
                <td style="font-size:12px;">${date}</td>
                <td><button class="action-btn" onclick="viewApplication(${app.id})">View</button></td>
            </tr>`;
        });

        html += '</tbody></table>';
        document.getElementById('applicationsTable').innerHTML = html;

        // Pagination
        let pagHtml = '';
        if (pag.total_pages > 1) {
            pagHtml = `<button ${currentAppPage <= 1 ? 'disabled' : ''} onclick="changeAppPage(${currentAppPage - 1})">← Prev</button>
                       <span class="page-info">Page ${pag.page} of ${pag.total_pages}</span>
                       <button ${currentAppPage >= pag.total_pages ? 'disabled' : ''} onclick="changeAppPage(${currentAppPage + 1})">Next →</button>`;
        }
        document.getElementById('pagination').innerHTML = pagHtml;

    } catch (err) {
        document.getElementById('applicationsTable').innerHTML = '<div class="loading" style="color:var(--danger)">Connection error.</div>';
    }
}

function changeAppPage(page) {
    currentAppPage = page;
    loadApplications();
}

// ===== VIEW APPLICATION =====
async function viewApplication(id) {
    try {
        const res = await fetch(API_BASE + 'api/admin_applications.php?id=' + id, { headers: authHeaders() });
        const data = await res.json();
        
        if (data.error) {
            alert('Error loading application details.');
            return;
        }

        const app = data.application;
        document.getElementById('modalTitle').textContent = 'Application: ' + app.ref_number;
        
        let html = `<div class="detail-grid">
            <div class="detail-item"><label>First Name</label><span>${app.first_name}</span></div>
            <div class="detail-item"><label>Last Name</label><span>${app.last_name}</span></div>
            <div class="detail-item"><label>Date of Birth</label><span>${app.date_of_birth}</span></div>
            <div class="detail-item"><label>Nationality</label><span>${app.nationality}</span></div>
            <div class="detail-item full"><label>Email</label><span>${app.email}</span></div>
            <div class="detail-item"><label>Phone</label><span>${app.phone || 'N/A'}</span></div>
            <div class="detail-item"><label>Country</label><span>${app.country}</span></div>
            <div class="detail-item"><label>School</label><span>${app.school}</span></div>
            <div class="detail-item full"><label>Year of Study</label><span>${app.year_of_study}</span></div>
            <div class="detail-item full"><label>T-Level Course</label><span>${app.tlevel_course}</span></div>
            <div class="detail-item"><label>Location</label><span>${app.preferred_location}</span></div>
            <div class="detail-item"><label>Start Date</label><span>${app.preferred_start}</span></div>
            <div class="detail-item"><label>Duration</label><span>${app.preferred_duration || 'Not specified'}</span></div>
            <div class="detail-item full"><label>Status</label><span class="status-badge status-${app.status}">${app.status}</span></div>
            <div class="detail-item full"><label>Submitted</label><span>${app.submitted_at}</span></div>
        </div>
        <div style="margin-top:16px;">
            <label style="font-size:13px;color:var(--muted);display:block;margin-bottom:6px;">Motivation</label>
            <p style="font-size:13px;line-height:1.6;color:var(--text);background:var(--bg);padding:12px;border-radius:6px;">${app.motivation}</p>
        </div>`;

        if (app.skills) {
            html += `<div style="margin-top:12px;">
                <label style="font-size:13px;color:var(--muted);display:block;margin-bottom:6px;">Skills & Experience</label>
                <p style="font-size:13px;background:var(--bg);padding:12px;border-radius:6px;">${app.skills}</p>
            </div>`;
        }

        // Admin controls
        html += `<div class="modal-actions" style="flex-direction:column;">
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <label style="font-size:13px;">Update Status:</label>
                <select id="updateStatus">
                    <option value="received" ${app.status === 'received' ? 'selected' : ''}>Received</option>
                    <option value="reviewing" ${app.status === 'reviewing' ? 'selected' : ''}>Reviewing</option>
                    <option value="shortlisted" ${app.status === 'shortlisted' ? 'selected' : ''}>Shortlisted</option>
                    <option value="interviewed" ${app.status === 'interviewed' ? 'selected' : ''}>Interviewed</option>
                    <option value="offered" ${app.status === 'offered' ? 'selected' : ''}>Offered</option>
                    <option value="accepted" ${app.status === 'accepted' ? 'selected' : ''}>Accepted</option>
                    <option value="rejected" ${app.status === 'rejected' ? 'selected' : ''}>Rejected</option>
                    <option value="withdrawn" ${app.status === 'withdrawn' ? 'selected' : ''}>Withdrawn</option>
                </select>
                <button class="save-btn" onclick="updateApplication(${app.id})">Save</button>
            </div>
            <textarea id="adminNotes" placeholder="Admin notes (internal only)">${app.admin_notes || ''}</textarea>
        </div>`;

        document.getElementById('modalBody').innerHTML = html;
        document.getElementById('detailModal').classList.add('active');

    } catch (err) {
        alert('Error loading application.');
    }
}

async function updateApplication(id) {
    const status = document.getElementById('updateStatus').value;
    const notes = document.getElementById('adminNotes').value.trim();

    try {
        const res = await fetch(API_BASE + 'api/admin_applications.php?id=' + id, {
            method: 'PUT',
            headers: authHeaders(),
            body: JSON.stringify({ status, admin_notes: notes })
        });
        const data = await res.json();

        if (data.error) {
            alert('Error: ' + data.message);
        } else {
            closeModal();
            loadApplications();
            loadDashboardStats();
        }
    } catch (err) {
        alert('Connection error.');
    }
}

function closeModal() {
    document.getElementById('detailModal').classList.remove('active');
}

// ===== ENQUIRIES =====
async function loadEnquiries() {
    try {
        const res = await fetch(API_BASE + 'api/admin_enquiries.php', { headers: authHeaders() });
        const data = await res.json();

        if (data.error) {
            document.getElementById('enquiriesTable').innerHTML = '<div class="loading" style="color:var(--danger)">Error loading enquiries.</div>';
            return;
        }

        const enquiries = data.enquiries || [];

        if (enquiries.length === 0) {
            document.getElementById('enquiriesTable').innerHTML = '<div class="loading">No enquiries yet.</div>';
            return;
        }

        let html = `<table><thead><tr>
            <th>Email</th><th>Subject</th><th>Status</th><th>Date</th><th></th>
        </tr></thead><tbody>`;

        enquiries.forEach(function(enq) {
            const statusClass = 'status-' + enq.status;
            const date = new Date(enq.submitted_at).toLocaleDateString();
            html += `<tr>
                <td style="font-size:12px;">${enq.email}</td>
                <td>${enq.subject}</td>
                <td><span class="status-badge ${statusClass}">${enq.status}</span></td>
                <td style="font-size:12px;">${date}</td>
                <td><button class="action-btn" onclick="viewEnquiry(${enq.id})">View</button></td>
            </tr>`;
        });

        html += '</tbody></table>';
        document.getElementById('enquiriesTable').innerHTML = html;

    } catch (err) {
        document.getElementById('enquiriesTable').innerHTML = '<div class="loading" style="color:var(--danger)">Connection error.</div>';
    }
}

async function viewEnquiry(id) {
    try {
        const res = await fetch(API_BASE + 'api/admin_enquiries.php?id=' + id, { headers: authHeaders() });
        const data = await res.json();
        
        if (data.error) {
            alert('Error loading enquiry details.');
            return;
        }

        const enq = data.enquiry;
        document.getElementById('modalTitle').textContent = 'Enquiry from ' + enq.email;
        
        let html = `<div class="detail-grid">
            <div class="detail-item full"><label>Email</label><span>${enq.email}</span></div>
            <div class="detail-item full"><label>Subject</label><span>${enq.subject}</span></div>
            <div class="detail-item full"><label>Message</label>
                <p style="font-size:13px;line-height:1.6;color:var(--text);background:var(--bg);padding:12px;border-radius:6px;margin-top:4px;">${enq.message}</p>
            </div>
            <div class="detail-item"><label>Status</label><span class="status-badge status-${enq.status}">${enq.status}</span></div>
            <div class="detail-item"><label>Submitted</label><span>${enq.submitted_at}</span></div>
        </div>
        <div class="modal-actions" style="flex-direction:column;margin-top:16px;">
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <label style="font-size:13px;">Update Status:</label>
                <select id="enquiryStatus">
                    <option value="unread" ${enq.status === 'unread' ? 'selected' : ''}>Unread</option>
                    <option value="read" ${enq.status === 'read' ? 'selected' : ''}>Read</option>
                    <option value="replied" ${enq.status === 'replied' ? 'selected' : ''}>Replied</option>
                    <option value="archived" ${enq.status === 'archived' ? 'selected' : ''}>Archived</option>
                </select>
                <button class="save-btn" onclick="updateEnquiry(${enq.id})">Save</button>
                <button class="delete-btn" onclick="deleteEnquiry(${enq.id})">Delete</button>
            </div>
        </div>`;

        document.getElementById('modalBody').innerHTML = html;
        document.getElementById('detailModal').classList.add('active');

    } catch (err) {
        alert('Error loading enquiry.');
    }
}

async function updateEnquiry(id) {
    const status = document.getElementById('enquiryStatus').value;

    try {
        const res = await fetch(API_BASE + 'api/admin_enquiries.php?id=' + id, {
            method: 'PUT',
            headers: authHeaders(),
            body: JSON.stringify({ status: status })
        });
        const data = await res.json();

        if (data.error) {
            alert('Error: ' + data.message);
        } else {
            closeModal();
            loadEnquiries();
            loadDashboardStats();
        }
    } catch (err) {
        alert('Connection error.');
    }
}

async function deleteEnquiry(id) {
    if (!confirm('Are you sure you want to delete this enquiry?')) return;

    try {
        const res = await fetch(API_BASE + 'api/admin_enquiries.php?id=' + id, {
            method: 'DELETE',
            headers: authHeaders()
        });
        const data = await res.json();

        if (data.error) {
            alert('Error: ' + data.message);
        } else {
            closeModal();
            loadEnquiries();
            loadDashboardStats();
        }
    } catch (err) {
        alert('Connection error.');
    }
}

// ===== FEEDBACK =====
let currentFeedbackPage = 1;

function ratingStars(rating) {
    const r = parseInt(rating, 10) || 0;
    return '★'.repeat(r) + '☆'.repeat(5 - r);
}

async function loadFeedback() {
    const rating = document.getElementById('feedbackRatingFilter').value;
    const status = document.getElementById('feedbackStatusFilter').value;
    const search = document.getElementById('feedbackSearchInput').value.trim();

    let url = API_BASE + 'api/admin_feedback.php?page=' + currentFeedbackPage + '&limit=15&sort_by=submitted_at&sort_order=DESC';
    if (rating) url += '&rating=' + encodeURIComponent(rating);
    if (status) url += '&status=' + encodeURIComponent(status);
    if (search) url += '&search=' + encodeURIComponent(search);

    try {
        const res = await fetch(url, { headers: authHeaders() });
        const data = await res.json();

        if (data.error) {
            document.getElementById('feedbackTable').innerHTML = '<div class="loading" style="color:var(--danger)">Error loading feedback.</div>';
            return;
        }

        const feedback = data.feedback || [];
        const pag = data.pagination || {};

        if (feedback.length === 0) {
            document.getElementById('feedbackTable').innerHTML = '<div class="loading">No feedback found.</div>';
            document.getElementById('feedbackPagination').innerHTML = '';
            return;
        }

        let html = `<table><thead><tr>
            <th>Rating</th><th>Email</th><th>Feedback</th><th>Status</th><th>Date</th><th></th>
        </tr></thead><tbody>`;

        feedback.forEach(function(fb) {
            const statusClass = 'status-' + fb.status;
            const date = new Date(fb.submitted_at).toLocaleDateString();
            const snippet = (fb.feedback_text || '').length > 80 ? fb.feedback_text.slice(0, 80) + '…' : (fb.feedback_text || '');
            html += `<tr>
                <td style="color:var(--accent);letter-spacing:1px;">${ratingStars(fb.rating)}</td>
                <td style="font-size:12px;">${fb.email || 'Anonymous'}</td>
                <td style="font-size:13px;">${snippet}</td>
                <td><span class="status-badge status-${fb.status}">${fb.status}</span></td>
                <td style="font-size:12px;">${date}</td>
                <td><button class="action-btn" onclick="viewFeedback(${fb.id})">View</button></td>
            </tr>`;
        });

        html += '</tbody></table>';
        document.getElementById('feedbackTable').innerHTML = html;

        let pagHtml = '';
        if (pag.total_pages > 1) {
            pagHtml = `<button ${currentFeedbackPage <= 1 ? 'disabled' : ''} onclick="changeFeedbackPage(${currentFeedbackPage - 1})">← Prev</button>
                       <span class="page-info">Page ${pag.page} of ${pag.total_pages}</span>
                       <button ${currentFeedbackPage >= pag.total_pages ? 'disabled' : ''} onclick="changeFeedbackPage(${currentFeedbackPage + 1})">Next →</button>`;
        }
        document.getElementById('feedbackPagination').innerHTML = pagHtml;

    } catch (err) {
        document.getElementById('feedbackTable').innerHTML = '<div class="loading" style="color:var(--danger)">Connection error.</div>';
    }
}

function changeFeedbackPage(page) {
    currentFeedbackPage = page;
    loadFeedback();
}

async function viewFeedback(id) {
    try {
        const res = await fetch(API_BASE + 'api/admin_feedback.php?id=' + id, { headers: authHeaders() });
        const data = await res.json();

        if (data.error) {
            alert('Error loading feedback details.');
            return;
        }

        const fb = data.feedback;
        document.getElementById('modalTitle').textContent = 'Feedback from ' + (fb.email || 'Anonymous');

        let html = `<div class="detail-grid">
            <div class="detail-item"><label>Rating</label><span style="color:var(--accent);letter-spacing:1px;">${ratingStars(fb.rating)}</span></div>
            <div class="detail-item"><label>Email</label><span>${fb.email || 'Anonymous'}</span></div>
            <div class="detail-item full"><label>Course / Area</label><span>${fb.course_name}</span></div>
            <div class="detail-item full"><label>Feedback</label>
                <p style="font-size:13px;line-height:1.6;color:var(--text);background:var(--bg);padding:12px;border-radius:6px;margin-top:4px;">${fb.feedback_text}</p>
            </div>
            <div class="detail-item"><label>Status</label><span class="status-badge status-${fb.status}">${fb.status}</span></div>
            <div class="detail-item"><label>Submitted</label><span>${fb.submitted_at}</span></div>
        </div>
        <div class="modal-actions" style="flex-direction:column;margin-top:16px;">
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <label style="font-size:13px;">Update Status:</label>
                <select id="feedbackStatus">
                    <option value="new" ${fb.status === 'new' ? 'selected' : ''}>New</option>
                    <option value="reviewed" ${fb.status === 'reviewed' ? 'selected' : ''}>Reviewed</option>
                    <option value="archived" ${fb.status === 'archived' ? 'selected' : ''}>Archived</option>
                </select>
                <button class="save-btn" onclick="saveFeedbackStatus(${fb.id})">Save</button>
                <button class="delete-btn" onclick="removeFeedback(${fb.id})">Delete</button>
            </div>
        </div>`;

        document.getElementById('modalBody').innerHTML = html;
        document.getElementById('detailModal').classList.add('active');

    } catch (err) {
        alert('Error loading feedback.');
    }
}

async function saveFeedbackStatus(id) {
    const status = document.getElementById('feedbackStatus').value;

    try {
        const res = await fetch(API_BASE + 'api/admin_feedback.php?id=' + id, {
            method: 'PUT',
            headers: authHeaders(),
            body: JSON.stringify({ status: status })
        });
        const data = await res.json();

        if (data.error) {
            alert('Error: ' + data.message);
        } else {
            closeModal();
            loadFeedback();
            loadDashboardStats();
        }
    } catch (err) {
        alert('Connection error.');
    }
}

async function removeFeedback(id) {
    if (!confirm('Are you sure you want to delete this feedback entry?')) return;

    try {
        const res = await fetch(API_BASE + 'api/admin_feedback.php?id=' + id, {
            method: 'DELETE',
            headers: authHeaders()
        });
        const data = await res.json();

        if (data.error) {
            alert('Error: ' + data.message);
        } else {
            closeModal();
            loadFeedback();
            loadDashboardStats();
        }
    } catch (err) {
        alert('Connection error.');
    }
}

// ===== REPORTS =====
async function loadReportStats() {
    try {
        const res = await fetch(API_BASE + 'api/admin_applications.php', { headers: authHeaders() });
        const data = await res.json();
        
        if (!data.error && data.applications) {
            const apps = data.applications;
            document.getElementById('reportTotal').textContent = data.pagination.total;
            
            // Count by sector
            const courses = {
                'Digital': ['Digital Production', 'Digital Support', 'Digital Business'],
                'Health & Science': ['Health', 'Healthcare'],
                'Construction': ['Building Services', 'Design, Surveying'],
                'Legal & Finance': ['Accounting', 'Finance'],
                'Education': ['Education']
            };
            
            let counts = { 'Digital': 0, 'Health & Science': 0, 'Construction': 0, 'Legal & Finance': 0, 'Education': 0 };
            apps.forEach(a => {
                const c = a.tlevel_course || '';
                if (c.includes('Digital')) counts['Digital']++;
                else if (c.includes('Health') || c.includes('Healthcare')) counts['Health & Science']++;
                else if (c.includes('Building') || c.includes('Design') || c.includes('Surveying')) counts['Construction']++;
                else if (c.includes('Accounting') || c.includes('Finance')) counts['Legal & Finance']++;
                else if (c.includes('Education')) counts['Education']++;
            });
            
            document.getElementById('reportDigital').textContent = counts['Digital'];
            document.getElementById('reportHealth').textContent = counts['Health & Science'];
            document.getElementById('reportConstruction').textContent = counts['Construction'];
            document.getElementById('reportFinance').textContent = counts['Legal & Finance'];
            document.getElementById('reportEducation').textContent = counts['Education'];
        }
    } catch (err) {}
}

function exportReport() {
    fetch(API_BASE + 'api/admin_applications.php?limit=1000', { headers: authHeaders() })
        .then(r => r.json())
        .then(data => {
            if (data.error) return;
            const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'tlevel_applications_export_' + new Date().toISOString().slice(0,10) + '.json';
            a.click();
            URL.revokeObjectURL(url);
        });
}

// Close modal on overlay click
document.getElementById('detailModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
</body>
</html>
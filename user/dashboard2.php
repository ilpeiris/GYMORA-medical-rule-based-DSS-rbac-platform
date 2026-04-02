<?php
// /carefit/user/dashboard.php
require_once '../config/db.php';
require_once '../config/session.php';
require_once '../config/constants.php';

requireRole(ROLE_USER);
$user_id = $_SESSION['user_id'];

// 1. Fetch user details + active package name
$stmt = $pdo->prepare("
    SELECT u.*, p.name as package_name 
    FROM users u 
    LEFT JOIN packages p ON u.package_id = p.id 
    WHERE u.id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// 2. Upcoming 1-on-1 Appointments
$apptStmt = $pdo->prepare("
    SELECT a.datetime, a.type, a.status, u.name as staff_name 
    FROM appointments a 
    JOIN users u ON a.staff_id = u.id 
    WHERE a.user_id = ? AND a.datetime >= CURDATE() AND a.status = 'scheduled' 
    ORDER BY a.datetime ASC LIMIT 5
");
$apptStmt->execute([$user_id]);
$appointments = $apptStmt->fetchAll();

// 3. Upcoming Booked Classes
$classStmt = $pdo->prepare("
    SELECT c.name, c.datetime, c.location, u.name as trainer_name 
    FROM bookings b 
    JOIN classes c ON b.class_id = c.id 
    JOIN users u ON c.trainer_id = u.id 
    WHERE b.user_id = ? AND b.status = 'confirmed' AND c.datetime >= NOW() 
    ORDER BY c.datetime ASC LIMIT 5
");
$classStmt->execute([$user_id]);
$booked_classes = $classStmt->fetchAll();

// 4. Get first name only for greeting
$first_name = explode(' ', trim($user['name']))[0];

// 5. Determine greeting by time of day
$hour = (int) date('G');
if ($hour < 12)      $greeting = 'Good morning';
elseif ($hour < 17)  $greeting = 'Good afternoon';
else                 $greeting = 'Good evening';
?>
<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>My Dashboard — CareFit</title>

    <!-- Google Fonts — same as Sassa template -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Sassa template assets — adjust path if your carefit folder differs -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">

    <!-- Bootstrap Icons (for the quick-action icons you already used) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        /* ─── CAREFIT PORTAL OVERRIDES ─────────────────────────────────── */

        /* CSS vars pulled from Sassa's color system */
        :root {
            --cf-purple:    #8B5CF6;   /* Sassa primary */
            --cf-purple-dk: #6D28D9;
            --cf-purple-lt: #EDE9FE;
            --cf-teal:      #0EA5E9;
            --cf-teal-lt:   #E0F2FE;
            --cf-green:     #10B981;
            --cf-green-lt:  #D1FAE5;
            --cf-amber:     #F59E0B;
            --cf-amber-lt:  #FEF3C7;
            --cf-red:       #EF4444;
            --cf-red-lt:    #FEE2E2;
            --cf-gray:      #64748B;
            --cf-body-bg:   #F8F7FF;  /* very light purple tint — matches Sassa hero bg */
            --cf-sidebar-w: 260px;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--cf-body-bg);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* ─── SIDEBAR ───────────────────────────────────────────────────── */
        .cf-sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--cf-sidebar-w);
            height: 100vh;
            background: #ffffff;
            border-right: 1px solid #EDE9FE;
            display: flex;
            flex-direction: column;
            z-index: 1000;
            overflow-y: auto;
        }

        .cf-sidebar-logo {
            padding: 28px 24px 20px;
            border-bottom: 1px solid #F3F0FF;
        }

        .cf-sidebar-logo .logo-text {
            font-family: 'Poppins', sans-serif;
            font-size: 22px;
            font-weight: 700;
            color: var(--cf-purple-dk);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cf-sidebar-logo .logo-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, var(--cf-purple), var(--cf-teal));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-size: 18px;
        }

        .cf-nav-section {
            padding: 20px 16px 8px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #A78BFA;
        }

        .cf-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            border-radius: 10px;
            margin: 2px 8px;
            font-size: 14.5px;
            font-weight: 500;
            color: var(--cf-gray);
            text-decoration: none;
            transition: background .15s, color .15s;
        }

        .cf-nav a i {
            font-size: 17px;
            width: 22px;
            text-align: center;
        }

        .cf-nav a:hover {
            background: var(--cf-purple-lt);
            color: var(--cf-purple-dk);
        }

        .cf-nav a.active {
            background: var(--cf-purple-lt);
            color: var(--cf-purple-dk);
            font-weight: 600;
        }

        .cf-nav a.active i { color: var(--cf-purple); }

        /* DSS badge on sidebar */
        .cf-dss-badge {
            margin: 16px 16px 0;
            padding: 14px 16px;
            background: linear-gradient(135deg, var(--cf-purple-lt), var(--cf-teal-lt));
            border-radius: 12px;
            border: 1px solid #DDD6FE;
        }

        .cf-dss-badge .badge-title {
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            font-weight: 600;
            color: var(--cf-purple-dk);
            margin-bottom: 4px;
        }

        .cf-dss-badge .badge-text {
            font-size: 11.5px;
            color: var(--cf-gray);
            line-height: 1.4;
        }

        /* Sidebar user chip at the bottom */
        .cf-sidebar-user {
            margin-top: auto;
            padding: 16px;
            border-top: 1px solid #F3F0FF;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cf-avatar {
            width: 38px; height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--cf-purple), var(--cf-teal));
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
        }

        .cf-sidebar-user .user-info { flex: 1; min-width: 0; }
        .cf-sidebar-user .user-name { font-size: 13.5px; font-weight: 600; color: #1E1B4B; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .cf-sidebar-user .user-role { font-size: 11px; color: var(--cf-gray); }

        .cf-sidebar-user .logout-btn {
            font-size: 17px;
            color: #A78BFA;
            text-decoration: none;
            transition: color .15s;
        }
        .cf-sidebar-user .logout-btn:hover { color: var(--cf-red); }

        /* ─── MAIN CONTENT ──────────────────────────────────────────────── */
        .cf-main {
            margin-left: var(--cf-sidebar-w);
            min-height: 100vh;
            padding: 0;
        }

        /* ─── TOP BAR ───────────────────────────────────────────────────── */
        .cf-topbar {
            background: #fff;
            border-bottom: 1px solid #EDE9FE;
            padding: 16px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .cf-topbar .page-title {
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            font-weight: 600;
            color: #1E1B4B;
            margin: 0;
        }

        .cf-topbar .breadcrumb {
            font-size: 12.5px;
            color: var(--cf-gray);
            margin: 0;
        }

        .cf-topbar .breadcrumb a { color: var(--cf-purple); text-decoration: none; }

        .cf-topbar-right { display: flex; align-items: center; gap: 14px; }

        .cf-topbar .notif-btn {
            width: 38px; height: 38px;
            border: 1.5px solid #EDE9FE;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: var(--cf-gray);
            text-decoration: none;
            font-size: 16px;
            transition: border-color .15s, color .15s;
        }
        .cf-topbar .notif-btn:hover { border-color: var(--cf-purple); color: var(--cf-purple); }

        /* ─── PAGE BODY ─────────────────────────────────────────────────── */
        .cf-content { padding: 28px 32px; }

        /* ─── HERO GREETING BANNER ──────────────────────────────────────── */
        .cf-greeting {
            background: linear-gradient(135deg, #6D28D9 0%, #0EA5E9 100%);
            border-radius: 16px;
            padding: 28px 32px;
            color: #fff;
            margin-bottom: 28px;
            position: relative;
            overflow: hidden;
        }

        .cf-greeting::after {
            content: '';
            position: absolute;
            right: -40px; top: -40px;
            width: 200px; height: 200px;
            background: rgba(255,255,255,.07);
            border-radius: 50%;
        }

        .cf-greeting::before {
            content: '';
            position: absolute;
            right: 60px; bottom: -60px;
            width: 150px; height: 150px;
            background: rgba(255,255,255,.05);
            border-radius: 50%;
        }

        .cf-greeting h2 {
            font-family: 'Poppins', sans-serif;
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 6px;
        }

        .cf-greeting p { margin: 0; opacity: .85; font-size: 14.5px; }

        .cf-greeting .dss-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 14px;
            background: rgba(255,255,255,.18);
            border: 1px solid rgba(255,255,255,.3);
            border-radius: 20px;
            padding: 5px 14px;
            font-size: 12.5px;
            font-weight: 500;
        }

        .cf-greeting .dss-pill i { font-size: 13px; }

        /* ─── SASSA-STYLE CARDS for dashboard content ───────────────────── */
        .cf-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #EDE9FE;
            overflow: hidden;
            height: 100%;
            transition: box-shadow .2s, transform .2s;
        }

        .cf-card:hover { box-shadow: 0 8px 30px rgba(109,40,217,.08); transform: translateY(-2px); }

        .cf-card-header {
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #F3F0FF;
        }

        .cf-card-header .header-icon {
            width: 34px; height: 34px;
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .cf-card-header h5 {
            font-family: 'Poppins', sans-serif;
            font-size: 15px;
            font-weight: 600;
            color: #1E1B4B;
            margin: 0;
        }

        .cf-card-body { padding: 20px; }

        /* Membership card stat items */
        .cf-stat-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #F8F7FF;
        }
        .cf-stat-row:last-child { border-bottom: none; padding-bottom: 0; }
        .cf-stat-label { font-size: 13px; color: var(--cf-gray); }
        .cf-stat-value { font-size: 14px; font-weight: 600; color: #1E1B4B; }

        .cf-consult-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: var(--cf-purple-lt);
            color: var(--cf-purple-dk);
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 13px;
            font-weight: 600;
        }

        /* ─── QUICK ACTION BUTTONS ──────────────────────────────────────── */
        .cf-quick-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 18px 10px;
            border-radius: 14px;
            border: 1.5px solid #EDE9FE;
            background: #fff;
            text-decoration: none;
            transition: all .2s;
            height: 100%;
        }

        .cf-quick-btn:hover {
            border-color: var(--cf-purple);
            background: var(--cf-purple-lt);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(109,40,217,.12);
        }

        .cf-quick-btn .btn-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
        }

        .cf-quick-btn .btn-label {
            font-size: 12.5px;
            font-weight: 600;
            color: #1E1B4B;
            text-align: center;
        }

        /* ─── LIST ITEMS (classes / appointments) ───────────────────────── */
        .cf-list-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 20px;
            border-bottom: 1px solid #F8F7FF;
            transition: background .15s;
        }
        .cf-list-item:last-child { border-bottom: none; }
        .cf-list-item:hover { background: #FDFCFF; }

        .cf-list-dot {
            width: 10px; height: 10px;
            border-radius: 50%;
            margin-top: 5px;
            flex-shrink: 0;
        }

        .cf-list-item .item-title {
            font-size: 14px;
            font-weight: 600;
            color: #1E1B4B;
            margin-bottom: 3px;
        }

        .cf-list-item .item-meta {
            font-size: 12px;
            color: var(--cf-gray);
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .cf-list-item .item-meta span { display: flex; align-items: center; gap: 4px; }

        /* Empty state */
        .cf-empty {
            padding: 32px 20px;
            text-align: center;
        }
        .cf-empty i { font-size: 32px; color: #DDD6FE; margin-bottom: 10px; display: block; }
        .cf-empty p { font-size: 13.5px; color: var(--cf-gray); margin: 0; }

        /* ─── SUCCESS ALERT ─────────────────────────────────────────────── */
        .cf-alert {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .cf-alert.success {
            background: var(--cf-green-lt);
            border: 1px solid #6EE7B7;
            color: #065F46;
        }
        .cf-alert i { font-size: 18px; margin-top: 1px; }

        /* ─── RESPONSIVE ────────────────────────────────────────────────── */
        @media (max-width: 991px) {
            .cf-sidebar { transform: translateX(-100%); transition: transform .3s; }
            .cf-sidebar.open { transform: translateX(0); }
            .cf-main { margin-left: 0; }
            .cf-topbar { padding: 14px 18px; }
            .cf-content { padding: 20px 18px; }
            .cf-mobile-toggle {
                display: flex !important;
                align-items: center;
                justify-content: center;
                width: 38px; height: 38px;
                border: 1.5px solid #EDE9FE;
                border-radius: 10px;
                color: var(--cf-purple-dk);
                font-size: 18px;
                cursor: pointer;
                background: none;
            }
        }
        @media (min-width: 992px) {
            .cf-mobile-toggle { display: none !important; }
        }
    </style>
</head>
<body>

<!-- ════════════════════════════════════════
     SIDEBAR
════════════════════════════════════════ -->
<aside class="cf-sidebar" id="cfSidebar">

    <!-- Logo -->
    <div class="cf-sidebar-logo">
        <a href="dashboard.php" class="logo-text">
            <div class="logo-icon"><i class="bi bi-heart-pulse-fill"></i></div>
            CareFit
        </a>
    </div>

    <!-- DSS Active Notice -->
    <div class="cf-dss-badge">
        <div class="badge-title"><i class="bi bi-shield-check me-1"></i> DSS Protection Active</div>
        <div class="badge-text">Your medical profile is safeguarding your exercise selections.</div>
    </div>

    <!-- Nav -->
    <nav class="cf-nav mt-2">
        <div class="cf-nav-section">My Portal</div>
        <a href="dashboard.php" class="active">
            <i class="bi bi-grid-1x2-fill"></i> Dashboard
        </a>
        <a href="workout_plan.php">
            <i class="bi bi-activity"></i> My Workout Plan
        </a>
        <a href="classes.php">
            <i class="bi bi-calendar-week"></i> Book a Class
        </a>
        <a href="progress.php">
            <i class="bi bi-graph-up-arrow"></i> My Progress
        </a>
        <a href="medical_report.php">
            <i class="bi bi-file-medical"></i> Medical Report
        </a>

        <div class="cf-nav-section">Account</div>
        <a href="appointments.php">
            <i class="bi bi-person-video3"></i> Appointments
        </a>
        <a href="chat.php">
            <i class="bi bi-chat-dots"></i> Messages
        </a>
        <a href="packages.php">
            <i class="bi bi-box-seam"></i> Packages
        </a>
        <a href="profile.php">
            <i class="bi bi-person-circle"></i> My Profile
        </a>
    </nav>

    <!-- User chip -->
    <div class="cf-sidebar-user">
        <div class="cf-avatar">
            <?= strtoupper(substr($user['name'], 0, 1)) ?>
        </div>
        <div class="user-info">
            <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
            <div class="user-role">Member</div>
        </div>
        <a href="../auth/logout.php" class="logout-btn" title="Logout">
            <i class="bi bi-box-arrow-right"></i>
        </a>
    </div>

</aside>

<!-- ════════════════════════════════════════
     MAIN CONTENT
════════════════════════════════════════ -->
<div class="cf-main">

    <!-- Top Bar -->
    <div class="cf-topbar">
        <div>
            <button class="cf-mobile-toggle" onclick="document.getElementById('cfSidebar').classList.toggle('open')">
                <i class="bi bi-list"></i>
            </button>
            <h1 class="page-title d-none d-md-block">Dashboard</h1>
            <div class="breadcrumb d-none d-md-flex">
                <a href="dashboard.php">Home</a>&nbsp;/&nbsp;Dashboard
            </div>
        </div>
        <div class="cf-topbar-right">
            <a href="chat.php" class="notif-btn" title="Messages">
                <i class="bi bi-chat-dots"></i>
            </a>
            <a href="appointments.php" class="notif-btn" title="Appointments">
                <i class="bi bi-bell"></i>
            </a>
            <!-- Avatar for mobile -->
            <div class="cf-avatar d-lg-none" style="width:34px;height:34px;font-size:13px;">
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
            </div>
        </div>
    </div>

    <!-- Page Content -->
    <div class="cf-content">

        <!-- ── SUCCESS ALERT ── -->
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'class_booked'): ?>
        <div class="cf-alert success">
            <i class="bi bi-patch-check-fill"></i>
            <div>
                <strong>Class booked successfully!</strong>
                Our DSS engine verified this class is medically safe for you to attend.
            </div>
        </div>
        <?php endif; ?>

        <!-- ── GREETING BANNER ── -->
        <div class="cf-greeting">
            <h2><?= $greeting ?>, <?= htmlspecialchars($first_name) ?>!</h2>
            <p>Here is your medical fitness overview for today,
                <?= date('l, F j') ?>.</p>
            <div class="dss-pill">
                <i class="bi bi-shield-fill-check"></i>
                DSS Engine Active — your workout plan is medically verified
            </div>
        </div>

        <!-- ── ROW 1: MEMBERSHIP + QUICK ACTIONS ── -->
        <div class="row g-4 mb-4">

            <!-- Membership Card -->
            <div class="col-md-5">
                <div class="cf-card">
                    <div class="cf-card-header">
                        <div class="header-icon" style="background:var(--cf-purple-lt);color:var(--cf-purple);">
                            <i class="bi bi-person-badge-fill"></i>
                        </div>
                        <h5>My Membership</h5>
                    </div>
                    <div class="cf-card-body">
                        <?php if ($user['package_id']): ?>
                            <div style="margin-bottom:16px;">
                                <span style="font-family:'Poppins',sans-serif;font-size:18px;font-weight:700;color:var(--cf-purple-dk);">
                                    <?= htmlspecialchars($user['package_name']) ?>
                                </span>
                            </div>
                            <div class="cf-stat-row">
                                <span class="cf-stat-label"><i class="bi bi-calendar-check me-1"></i> Expires on</span>
                                <span class="cf-stat-value"><?= date('M j, Y', strtotime($user['package_expiry'])) ?></span>
                            </div>
                            <div class="cf-stat-row">
                                <span class="cf-stat-label"><i class="bi bi-stethoscope me-1"></i> Consultations left</span>
                                <span class="cf-consult-badge">
                                    <i class="bi bi-plus-circle-fill"></i>
                                    <?= $user['consultations_remaining'] ?> remaining
                                </span>
                            </div>
                            <div class="cf-stat-row">
                                <span class="cf-stat-label"><i class="bi bi-circle-fill me-1" style="color:var(--cf-green);font-size:8px;"></i> Status</span>
                                <span class="cf-stat-value" style="color:var(--cf-green);">Active</span>
                            </div>
                            <a href="packages.php" class="th-btn mt-3 d-block text-center"
                               style="border-radius:10px;padding:10px 20px;font-size:13.5px;">
                                Manage Package
                            </a>
                        <?php else: ?>
                            <div class="cf-empty">
                                <i class="bi bi-box-seam"></i>
                                <p>No active membership package.</p>
                                <a href="packages.php" class="th-btn mt-3"
                                   style="border-radius:10px;padding:10px 24px;font-size:13.5px;">
                                    Browse Packages
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-md-7">
                <div class="cf-card">
                    <div class="cf-card-header">
                        <div class="header-icon" style="background:var(--cf-teal-lt);color:var(--cf-teal);">
                            <i class="bi bi-lightning-charge-fill"></i>
                        </div>
                        <h5>Quick Actions</h5>
                    </div>
                    <div class="cf-card-body">
                        <div class="row g-3">
                            <div class="col-6 col-sm-3">
                                <a href="classes.php" class="cf-quick-btn">
                                    <div class="btn-icon" style="background:var(--cf-green-lt);color:var(--cf-green);">
                                        <i class="bi bi-calendar-plus-fill"></i>
                                    </div>
                                    <span class="btn-label">Book a Class</span>
                                </a>
                            </div>
                            <div class="col-6 col-sm-3">
                                <a href="appointments.php" class="cf-quick-btn">
                                    <div class="btn-icon" style="background:var(--cf-teal-lt);color:var(--cf-teal);">
                                        <i class="bi bi-person-video3"></i>
                                    </div>
                                    <span class="btn-label">Book Appointment</span>
                                </a>
                            </div>
                            <div class="col-6 col-sm-3">
                                <a href="workout_plan.php" class="cf-quick-btn">
                                    <div class="btn-icon" style="background:var(--cf-purple-lt);color:var(--cf-purple);">
                                        <i class="bi bi-activity"></i>
                                    </div>
                                    <span class="btn-label">Workout Plan</span>
                                </a>
                            </div>
                            <div class="col-6 col-sm-3">
                                <a href="progress.php" class="cf-quick-btn">
                                    <div class="btn-icon" style="background:var(--cf-amber-lt);color:var(--cf-amber);">
                                        <i class="bi bi-graph-up-arrow"></i>
                                    </div>
                                    <span class="btn-label">Log Progress</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /row 1 -->

        <!-- ── ROW 2: UPCOMING CLASSES + APPOINTMENTS ── -->
        <div class="row g-4">

            <!-- Upcoming Classes -->
            <div class="col-md-6">
                <div class="cf-card">
                    <div class="cf-card-header">
                        <div class="header-icon" style="background:var(--cf-green-lt);color:var(--cf-green);">
                            <i class="bi bi-calendar-event-fill"></i>
                        </div>
                        <h5>Upcoming Classes</h5>
                    </div>

                    <?php if (count($booked_classes) > 0): ?>
                        <?php foreach ($booked_classes as $c): ?>
                        <div class="cf-list-item">
                            <div class="cf-list-dot" style="background:var(--cf-green);"></div>
                            <div>
                                <div class="item-title"><?= htmlspecialchars($c['name']) ?></div>
                                <div class="item-meta">
                                    <span><i class="bi bi-clock"></i>
                                        <?= date('D, M j @ g:i A', strtotime($c['datetime'])) ?>
                                    </span>
                                    <span><i class="bi bi-geo-alt"></i>
                                        <?= htmlspecialchars($c['location']) ?>
                                    </span>
                                    <span><i class="bi bi-person"></i>
                                        <?= htmlspecialchars($c['trainer_name']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <div style="padding:12px 20px;border-top:1px solid #F8F7FF;">
                            <a href="classes.php" style="font-size:13px;color:var(--cf-purple);font-weight:600;text-decoration:none;">
                                Browse more classes <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="cf-empty">
                            <i class="bi bi-calendar-x"></i>
                            <p>No upcoming classes booked.</p>
                            <a href="classes.php" class="th-btn mt-2"
                               style="border-radius:10px;padding:9px 20px;font-size:13px;">
                                Browse Classes
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 1-on-1 Appointments -->
            <div class="col-md-6">
                <div class="cf-card">
                    <div class="cf-card-header">
                        <div class="header-icon" style="background:var(--cf-teal-lt);color:var(--cf-teal);">
                            <i class="bi bi-person-video"></i>
                        </div>
                        <h5>1-on-1 Appointments</h5>
                    </div>

                    <?php if (count($appointments) > 0): ?>
                        <?php foreach ($appointments as $a): ?>
                        <div class="cf-list-item">
                            <div class="cf-list-dot" style="background:var(--cf-teal);"></div>
                            <div>
                                <div class="item-title">
                                    <?= ucwords(str_replace('_', ' ', $a['type'])) ?>
                                </div>
                                <div class="item-meta">
                                    <span><i class="bi bi-person-badge"></i>
                                        With <?= htmlspecialchars($a['staff_name']) ?>
                                    </span>
                                    <span><i class="bi bi-clock"></i>
                                        <?= date('D, M j @ g:i A', strtotime($a['datetime'])) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <div style="padding:12px 20px;border-top:1px solid #F8F7FF;">
                            <a href="appointments.php" style="font-size:13px;color:var(--cf-purple);font-weight:600;text-decoration:none;">
                                Manage appointments <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="cf-empty">
                            <i class="bi bi-person-slash"></i>
                            <p>No appointments scheduled yet.</p>
                            <a href="appointments.php" class="th-btn mt-2"
                               style="border-radius:10px;padding:9px 20px;font-size:13px;">
                                Book Appointment
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- /row 2 -->

    </div><!-- /cf-content -->
</div><!-- /cf-main -->

<!-- Sassa JS -->
<script src="../assets/js/vendor/jquery-3.7.1.min.js"></script>
<script src="../assets/js/bootstrap.min.js"></script>

<!-- Mobile sidebar overlay close -->
<script>
document.addEventListener('click', function(e) {
    var sidebar = document.getElementById('cfSidebar');
    var toggle  = document.querySelector('.cf-mobile-toggle');
    if (sidebar.classList.contains('open') && !sidebar.contains(e.target) && e.target !== toggle) {
        sidebar.classList.remove('open');
    }
});
</script>

</body>
</html>

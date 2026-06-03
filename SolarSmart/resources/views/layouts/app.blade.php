<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'SolarSmart') - Solar Energy Monitoring</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Animate.css -->
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-iBBXm8fW90+nuLcSKlbmrPcLa0OT92xO1BIsZ+ywDWZCvqsWgccV3gFoRBv0z+8dLJgyAHIhR35VZc2oM/gI1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Line Awesome -->
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">

    <!-- Custom Styles - Light Professional Design System -->
    <style>
        :root {
            --bg: #F1F5F9;
            --surface: #FFFFFF;
            --surface-hover: #F8FAFC;
            --surface-elevated: #F1F5F9;
            --border: #E2E8F0;
            --border-hover: #CBD5E1;

            --solar-amber: #F59E0B;
            --solar-amber-light: #FBBF24;
            --solar-amber-dark: #D97706;
            --solar-orange: #EA580C;
            --blue-accent: #3B82F6;
            --blue-light: #60A5FA;
            --blue-dark: #2563EB;

            --text-primary: #0F172A;
            --text-secondary: #475569;
            --text-muted: #94A3B8;
            --text-dim: #CBD5E1;

            --success: #059669;
            --success-bg: #ECFDF5;
            --success-border: #A7F3D0;
            --warning: #D97706;
            --warning-bg: #FFFBEB;
            --warning-border: #FDE68A;
            --danger: #DC2626;
            --danger-bg: #FEF2F2;
            --danger-border: #FECACA;
            --info: #2563EB;
            --info-bg: #EFF6FF;
            --info-border: #BFDBFE;

            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.08), 0 1px 2px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08), 0 2px 4px rgba(0, 0, 0, 0.04);
            --shadow-lg: 0 8px 30px rgba(0, 0, 0, 0.1), 0 4px 8px rgba(0, 0, 0, 0.04);

            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 14px;
            --radius-xl: 20px;
            --radius-full: 9999px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Figtree', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg);
            min-height: 100vh;
            color: var(--text-primary);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        .animated-bg { background: var(--bg); }

        /* ========== HEADER (UDES Style) ========== */
        .header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
        }

        .top-header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0.75rem 0;
        }

        .top-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .top-nav .logo img {
            height: 60px;
            width: auto;
        }

        .republique {
            flex: 1;
            text-align: center;
            padding: 0 1rem;
        }

        .republique p {
            margin: 0;
            font-size: 0.85rem;
            color: var(--text-secondary);
            line-height: 1.4;
        }

        .republique h2 {
            margin: 0.25rem 0 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .top-nav .info h3 {
            margin: 0;
            font-size: 0.9rem;
            text-align: right;
        }

        .top-nav .info h3 a {
            color: var(--text-secondary);
            text-decoration: none;
            display: block;
            margin-bottom: 0.25rem;
        }

        .top-nav .info h3 a:hover {
            color: var(--blue-accent);
        }

        .menu-toggle {
            display: none;
            cursor: pointer;
            font-size: 1.5rem;
            color: var(--text-primary);
        }

        .la-bars { display: block; }
        .la-times { display: none; }

        /* Bottom Navigation */
        .fixed-position {
            position: sticky;
            top: 0;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            z-index: 1000;
        }

        .navigation {
            width: 100%;
        }

        .menu {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            flex-wrap: wrap;
        }

        .menu-item {
            position: relative;
        }

        .menu-item a {
            display: block;
            padding: 1rem 1.25rem;
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
            border-bottom: 3px solid transparent;
        }

        .menu-item a:hover {
            color: var(--text-primary);
            background: var(--surface-hover);
            border-bottom-color: var(--blue-accent);
        }

        .menu-item.active a {
            color: var(--blue-accent);
            border-bottom-color: var(--blue-accent);
        }

        .sub-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sub-menu {
            position: absolute;
            top: 100%;
            left: 0;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-lg);
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s ease;
            z-index: 1000;
            padding: 0.5rem 0;
        }

        .menu-item:hover .sub-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .sub-item a {
            padding: 0.75rem 1.25rem;
            border: none;
            border-bottom: 1px solid var(--border);
        }

        .sub-item:last-child a {
            border-bottom: none;
        }

        .more {
            position: relative;
        }

        .more-menu {
            position: absolute;
            top: 0;
            left: 100%;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-lg);
            min-width: 180px;
            opacity: 0;
            visibility: hidden;
            transform: translateX(10px);
            transition: all 0.3s ease;
            z-index: 1001;
            padding: 0.5rem 0;
        }

        .more:hover .more-menu {
            opacity: 1;
            visibility: visible;
            transform: translateX(0);
        }

        .more-btn {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .more-btn::after {
            content: "\f107";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            margin-left: auto;
        }

        /* ========== USER DROPDOWN (kept in new location) ========== */
        .user-section {
            margin-left: auto;
            padding: 0 1rem;
        }

        .user-section .user-dropdown {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-secondary);
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-md);
            transition: all 0.2s ease;
        }

        .user-section .user-dropdown:hover {
            background: var(--surface-hover);
            color: var(--text-primary);
        }

        /* ========== FOOTER ========== */
        .footer {
            background: var(--surface);
            color: var(--text-muted);
            padding: 1.25rem 0;
            text-align: center;
            border-top: 1px solid var(--border);
            font-size: 0.85rem;
        }
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            transition: all 0.25s ease;
        }
        .card:hover { border-color: var(--border-hover); box-shadow: var(--shadow-md); }

        .card-header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            font-weight: 700;
            padding: 1rem 1.25rem;
            font-size: 1rem;
            color: var(--text-primary);
            letter-spacing: -0.01em;
        }

        .card-body { padding: 1.25rem; background: var(--surface); }

        /* ========== STAT CARDS ========== */
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 1.25rem;
            box-shadow: var(--shadow-sm);
            transition: all 0.25s ease;
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: var(--blue-accent);
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); border-color: var(--border-hover); }

        .stat-card h3 { font-size: 1.75rem; font-weight: 800; letter-spacing: -0.02em; color: var(--text-primary); }
        .stat-card small { font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary); }

        .stat-icon {
            width: 48px; height: 48px;
            border-radius: var(--radius-md);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
            background: var(--info-bg);
            color: var(--blue-accent);
            border: 1px solid var(--info-border);
        }
        .stat-icon.cyan { background: var(--info-bg); color: var(--blue-accent); border-color: var(--info-border); }
        .stat-icon.success { background: var(--success-bg); color: var(--success); border-color: var(--success-border); }
        .stat-icon.danger { background: var(--danger-bg); color: var(--danger); border-color: var(--danger-border); }

        /* ========== BUTTONS ========== */
        .btn {
            font-weight: 600;
            padding: 0.625rem 1.5rem;
            border-radius: var(--radius-md);
            transition: all 0.2s ease;
            border: none;
            font-size: 0.875rem;
            letter-spacing: -0.01em;
        }
        .btn:active { transform: scale(0.97); }

        .btn-primary {
            background: var(--blue-accent);
            color: #fff !important;
            box-shadow: 0 1px 3px rgba(59, 130, 246, 0.3);
        }
        .btn-primary:hover { background: var(--blue-dark); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); transform: translateY(-1px); color: #fff !important; }

        .btn-secondary { background: var(--surface-elevated); color: var(--text-secondary) !important; border: 1px solid var(--border); }
        .btn-secondary:hover { background: var(--border); color: var(--text-primary) !important; transform: translateY(-1px); }

        .btn-success { background: var(--success); color: #fff !important; }
        .btn-success:hover { background: #047857; color: #fff !important; transform: translateY(-1px); }

        .btn-danger { background: var(--danger); color: #fff !important; }
        .btn-danger:hover { background: #B91C1C; color: #fff !important; transform: translateY(-1px); }

        .btn-warning { background: var(--warning); color: #fff !important; }
        .btn-warning:hover { background: var(--solar-amber-dark); color: #fff !important; }

        .btn-info { background: var(--info); color: #fff !important; }
        .btn-info:hover { background: var(--blue-dark); color: #fff !important; }

        .btn-outline-primary {
            color: var(--blue-accent);
            border: 1.5px solid var(--info-border);
            background: transparent;
        }
        .btn-outline-primary:hover {
            background: var(--info-bg);
            border-color: var(--blue-accent);
            color: var(--blue-accent) !important;
        }
        .btn-outline-secondary { color: var(--text-secondary); border: 1.5px solid var(--border); background: transparent; }
        .btn-outline-secondary:hover { background: var(--surface-hover); color: var(--text-primary) !important; border-color: var(--border-hover); }

        .btn-outline-success { color: var(--success); border: 1.5px solid var(--success-border); background: transparent; }
        .btn-outline-success:hover { background: var(--success-bg); color: var(--success) !important; }
        .btn-outline-danger { color: var(--danger); border: 1.5px solid var(--danger-border); background: transparent; }
        .btn-outline-danger:hover { background: var(--danger-bg); color: var(--danger) !important; }
        .btn-outline-warning { color: var(--warning); border: 1.5px solid var(--warning-border); background: transparent; }
        .btn-outline-warning:hover { background: var(--warning-bg); color: var(--warning) !important; }
        .btn-outline-info { color: var(--info); border: 1.5px solid var(--info-border); background: transparent; }
        .btn-outline-info:hover { background: var(--info-bg); color: var(--info) !important; }

        .btn-sm { padding: 0.4rem 0.875rem; font-size: 0.8rem; }
        .btn-lg { padding: 0.875rem 2rem; font-size: 1rem; }

        .btn-weather {
            background: var(--blue-accent);
            color: #fff !important;
        }
        .btn-weather:hover { background: var(--blue-dark); color: #fff !important; transform: translateY(-1px); }

        /* ========== TABLES ========== */
        .table {
            border-radius: var(--radius-lg);
            overflow: hidden;
            margin-bottom: 0;
            color: var(--text-primary);
        }
        .table thead { background: var(--surface-elevated); }
        .table th {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.08em;
            padding: 0.875rem 1rem;
            border: none;
            color: var(--text-muted);
            border-bottom: 2px solid var(--border);
        }
        .table td {
            padding: 0.875rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--border);
            color: var(--text-secondary);
        }
        .table tbody { background: var(--surface); }
        .table tbody tr { transition: background 0.15s ease; }
        .table tbody tr:hover { background: var(--surface-hover); }
        .table-striped > tbody > tr:nth-of-type(odd) > * { background: var(--bg); }

        /* ========== BADGES ========== */
        .badge {
            font-weight: 600;
            padding: 0.35em 0.75em;
            border-radius: var(--radius-sm);
            font-size: 0.7rem;
            letter-spacing: 0.02em;
        }
        .bg-success { background: var(--success-bg) !important; color: var(--success) !important; border: 1px solid var(--success-border); }
        .bg-warning { background: var(--warning-bg) !important; color: var(--warning) !important; border: 1px solid var(--warning-border); }
        .bg-danger { background: var(--danger-bg) !important; color: var(--danger) !important; border: 1px solid var(--danger-border); }
        .bg-info { background: var(--info-bg) !important; color: var(--info) !important; border: 1px solid var(--info-border); }
        .bg-secondary { background: var(--surface-elevated) !important; color: var(--text-secondary) !important; border: 1px solid var(--border); }
        .bg-primary { background: var(--info-bg) !important; color: var(--blue-accent) !important; border: 1px solid var(--info-border); }

        .badge-active {
            background: var(--success-bg) !important;
            color: var(--success) !important;
            padding: 0.35em 0.85em;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 0.7rem;
            border: 1px solid var(--success-border);
        }
        .badge-inactive {
            background: var(--surface-elevated) !important;
            color: var(--text-muted) !important;
            padding: 0.35em 0.85em;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 0.7rem;
            border: 1px solid var(--border);
        }
        .badge-maintenance {
            background: var(--warning-bg) !important;
            color: var(--warning) !important;
            padding: 0.35em 0.85em;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 0.7rem;
            border: 1px solid var(--warning-border);
        }

        /* ========== BOOTSTRAP TEXT OVERRIDES ========== */
        .text-muted { color: var(--text-muted) !important; }
        .text-primary { color: var(--blue-accent) !important; }
        .text-secondary { color: var(--text-secondary) !important; }
        .text-success { color: var(--success) !important; }
        .text-danger { color: var(--danger) !important; }
        .text-warning { color: var(--warning) !important; }
        .text-info { color: var(--info) !important; }
        .text-light { color: var(--text-secondary) !important; }
        .text-dark { color: var(--text-primary) !important; }

        .card-body { color: var(--text-primary) !important; }
        .card-body strong { color: var(--text-primary) !important; }
        .card-body span { color: var(--text-primary) !important; }
        .card-body small { color: var(--text-secondary) !important; }

        .card-footer { color: var(--text-secondary) !important; border-top-color: var(--border) !important; background: var(--surface) !important; }

        /* ========== BOOTSTRAP ALERT OVERRIDES ========== */
        .alert { color: var(--text-primary) !important; }
        .alert-success { background: var(--success-bg) !important; color: var(--success) !important; border: 1px solid var(--success-border) !important; }
        .alert-danger { background: var(--danger-bg) !important; color: var(--danger) !important; border: 1px solid var(--danger-border) !important; }
        .alert-warning { background: var(--warning-bg) !important; color: var(--warning) !important; border: 1px solid var(--warning-border) !important; }
        .alert-info { background: var(--info-bg) !important; color: var(--info) !important; border: 1px solid var(--info-border) !important; }

        .alert-badge {
            position: absolute;
            top: -4px; right: -4px;
            background: var(--danger);
            color: #fff;
            font-size: 0.65rem;
            padding: 0.2rem 0.45rem;
            border-radius: var(--radius-full);
        }

        /* ========== NOTIFICATIONS ========== */
        .notification-dropdown { width: 380px; max-height: 500px; padding: 0; background: var(--surface); border: 1px solid var(--border); }
        .notification-header {
            display: flex; justify-content: space-between; align-items: center;
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
            background: var(--surface);
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        }
        .notification-header h6 { font-weight: 700; color: var(--text-primary); }
        .notification-list { max-height: 350px; overflow-y: auto; }
        .notification-section-title {
            padding: 8px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.08em; color: var(--text-muted); background: var(--bg);
        }
        .notification-item {
            display: flex; gap: 12px; padding: 12px 16px;
            border-bottom: 1px solid var(--border);
            transition: background 0.15s; text-decoration: none;
        }
        .notification-item:hover { background: var(--surface-hover); }
        .notification-icon {
            width: 36px; height: 36px; border-radius: 50%;
            background: var(--surface-elevated);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .notification-content { flex: 1; min-width: 0; }
        .notification-title { font-weight: 600; color: var(--text-primary); font-size: 13px; }
        .notification-message { font-size: 12px; color: var(--text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .notification-time { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
        .notification-empty { text-align: center; padding: 40px 20px; color: var(--text-secondary); }
        .notification-empty i { font-size: 36px; margin-bottom: 8px; color: var(--text-muted); }
        .notification-footer { padding: 12px 16px; border-top: 1px solid var(--border); background: var(--bg); border-radius: 0 0 var(--radius-lg) var(--radius-lg); }

        .alert-critical .notification-icon { background: var(--danger-bg); }
        .alert-high .notification-icon { background: var(--warning-bg); }
        .alert-medium .notification-icon { background: var(--info-bg); }
        .alert-low .notification-icon { background: var(--success-bg); }

        /* ========== PRODUCTION INDICATOR ========== */
        .production-indicator { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
        .production-good { background-color: var(--success); }
        .production-warning { background-color: var(--warning); }
        .production-danger { background-color: var(--danger); }

        /* ========== PAGE HEADERS ========== */
        .page-header {
            background: var(--surface);
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            margin-bottom: 1.5rem;
            border: 1px solid var(--border);
        }
        .page-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 0.375rem;
            letter-spacing: -0.02em;
        }
        .page-subtitle { color: var(--text-secondary); font-size: 0.95rem; }

        /* ========== FORM CONTROLS ========== */
        .form-control, .form-select {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: var(--radius-md);
            padding: 0.625rem 1rem;
            color: var(--text-primary);
            transition: all 0.2s ease;
            font-size: 0.9rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--blue-accent);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
            background: var(--surface);
            color: var(--text-primary);
        }
        .form-control::placeholder { color: var(--text-muted); }
        .form-label { font-weight: 600; color: var(--text-primary); margin-bottom: 0.5rem; font-size: 0.85rem; }

        /* ========== ALERTS ========== */
        .alert { border-radius: var(--radius-md); border: none; padding: 1rem 1.25rem; font-weight: 500; }

        /* ========== MAIN CONTENT ========== */
        .main-content {
            background: var(--bg);
            min-height: calc(100vh - 140px);
            padding: 2rem;
        }

        /* Mobile Menu */
        @media (max-width: 768px) {
            .menu {
                display: none;
                flex-direction: column;
            }
            .menu.show {
                display: flex;
            }
            .menu-item {
                width: 100%;
            }
            .menu-item a {
                padding: 0.75rem 1rem;
            }
            .sub-menu {
                position: static;
                opacity: 1;
                visibility: visible;
                transform: none;
                box-shadow: none;
                border: none;
                background: var(--surface-elevated);
            }
            .main-content { padding: 1rem; }
        }

        /* ========== ANIMATIONS ========== */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in { animation: fadeInUp 0.5s ease forwards; }
        .animate-delay-1 { animation-delay: 0.1s; }
        .animate-delay-2 { animation-delay: 0.2s; }
        .animate-delay-3 { animation-delay: 0.3s; }
        .animate-delay-4 { animation-delay: 0.4s; }

        /* ========== WEATHER CARDS ========== */
        .weather-card-main {
            background: linear-gradient(135deg, var(--blue-accent), var(--blue-dark));
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            color: white;
            text-align: center;
            height: 100%;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
        }
        .weather-icon-large { font-size: 3.5rem; margin-bottom: 0.5rem; }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
        .weather-icon-large { animation: float 3s ease-in-out infinite; }
        .weather-card-main .temperature { font-size: 3rem; font-weight: 800; line-height: 1; }
        .weather-card-main .condition { font-size: 1.25rem; font-weight: 500; margin: 0.5rem 0; }
        .weather-card-main .feels-like { font-size: 0.9rem; opacity: 0.85; }

        .weather-info-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 1rem;
            text-align: center;
            height: 100%;
            transition: all 0.2s ease;
        }
        .weather-info-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); border-color: var(--border-hover); }
        .weather-info-icon { font-size: 1.5rem; color: var(--blue-accent); margin-bottom: 0.5rem; }
        .weather-info-label { font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.25rem; letter-spacing: 0.06em; font-weight: 600; }
        .weather-info-value { font-size: 1.25rem; font-weight: 700; color: var(--text-primary); }
        .weather-info-card.production-impact { border-color: var(--info-border); }
        .weather-info-card.production-impact .weather-info-icon { color: var(--blue-accent); }

        .impact-bar { height: 6px; background: var(--info-bg); border-radius: 3px; margin-top: 0.5rem; overflow: hidden; }
        .impact-fill { height: 100%; background: var(--blue-accent); border-radius: 3px; transition: width 0.5s ease; }

        /* ========== SCROLLBAR ========== */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: var(--bg); }
        ::-webkit-scrollbar-thumb { background: var(--border-hover); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--blue-accent); }

        /* ========== DARK THEME OVERRIDES ========== */
        [data-bs-theme="dark"] {
            --bg: #0F172A;
            --surface: #1E293B;
            --surface-hover: #334155;
            --surface-elevated: #1E293B;
            --border: #334155;
            --border-hover: #475569;
            --text-primary: #F8FAFC;
            --text-secondary: #CBD5E1;
            --text-muted: #64748B;
            --text-dim: #475569;
        }

        [data-bs-theme="dark"] body {
            background: var(--bg);
            color: var(--text-primary);
        }

        [data-bs-theme="dark"] .header,
        [data-bs-theme="dark"] .fixed-position,
        [data-bs-theme="dark"] .top-header {
            background: var(--surface);
            border-color: var(--border);
        }

        [data-bs-theme="dark"] .card,
        [data-bs-theme="dark"] .stat-card,
        [data-bs-theme="dark"] .weather-card,
        [data-bs-theme="dark"] .systems-card,
        [data-bs-theme="dark"] .alerts-card {
            background: var(--surface);
            border-color: var(--border);
        }

        [data-bs-theme="dark"] .table thead {
            background: var(--surface-elevated);
        }

        [data-bs-theme="dark"] .table tbody {
            background: var(--surface);
        }

        [data-bs-theme="dark"] .table-striped > tbody > tr:nth-of-type(odd) > * {
            background: var(--surface-elevated);
        }

        [data-bs-theme="dark"] .main-content {
            background: var(--bg);
        }

        [data-bs-theme="dark"] .footer {
            background: var(--surface);
            border-top-color: var(--border);
        }
    </style>

    @stack('styles')
</head>
<body class="animated-bg" data-bs-theme="dark">
    <div class="header">
      <div class="top-header">
          <div class="container">
<nav class="top-nav">
                   <a href="{{ route('dashboard') }}" class="logo"><i class="bi bi-sun-fill" style="font-size: 48px; color: var(--solar-amber);"></i></a>
<div class="republique">
                        <p>Direction Générale de la Recherche Scientifique</p>
                        <p>Unité de Développement des Équipements Solaires</p>
                        <h2><span style="color: var(--solar-amber);">Solar</span>Smart - Solar Energy Monitoring</h2>
                    </div>
                    <div class="info">
                      <h3>
                        <a href="{{ route('alerts.index') }}">{{ __('Alerts') }}</a><br>
                        <a href="{{ route('interventions.index') }}">{{ __('Interventions') }}</a><br>
                        <a href="{{ route('weather.index') }}">Weather</a><br>
                        <a href="https://mail.cder.dz/" target="_blank">Mail</a>
                      </h3>
                      <font size="4">
                      <a target="_blank" href="https://www.facebook.com/">
                        <i class="fab fa-facebook"></i>
                      </a>
                      &nbsp;&nbsp;
<a target="_blank" href="https://www.youtube.com/@unitededeveloppementdesequ3473">
                         <i class="fab fa-youtube"></i>
                       </a>
                      &nbsp;&nbsp;
                      <a target="_blank" href="#">
                        <i class="fab fa-linkedin"></i>
                      </a>
                      &nbsp;&nbsp;
                      <a target="_blank" href="#">
                        <i class="fab fa-twitter"></i>
                      </a>
                      </font>
                    </div>
                  <div id="menu-toggle" class="menu-toggle">
                      <i id="menu-open" class="las la-bars"></i>
                      <i id="menu-close" class="las la-times"></i>
                  </div>
              </nav>
          </div>
      </div>

<!-- Bottom Navigation -->
      <header class="fixed-position">
        <div class="container">
          <div class="navigation">
            <ul class="menu">
              <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}">{{ __('Home') }}</a>
              </li>
              <li class="menu-item {{ request()->routeIs('solar-systems.*') && !request()->routeIs('solar-systems.panels.*') && !request()->routeIs('solar-systems.productions.*') && !request()->routeIs('solar-systems.interventions.*') && !request()->routeIs('solar-systems.fault-simulations.*') ? 'active' : '' }}">
                <a href="{{ route('solar-systems.index') }}">{{ __('Solar Systems') }}</a>
              </li>
              <li class="menu-item {{ request()->routeIs('solar-systems.panels.*', 'panels.index') ? 'active' : '' }}">
                <a href="{{ route('panels.index') }}">{{ __('Panels') }}</a>
              </li>
              <li class="menu-item {{ request()->routeIs('solar-systems.productions.*', 'productions.index', 'production.index') ? 'active' : '' }}">
                <a href="{{ route('productions.index') }}">{{ __('Production') }}</a>
              </li>
              <li class="menu-item {{ request()->routeIs('alerts.*') ? 'active' : '' }}">
                <a href="{{ route('alerts.index') }}">{{ __('Alerts') }}</a>
              </li>
              <li class="menu-item {{ request()->routeIs('solar-systems.interventions.*', 'interventions.*') ? 'active' : '' }}">
                <a href="{{ route('interventions.index') }}">{{ __('Interventions') }}</a>
              </li>
<li class="menu-item {{ request()->routeIs('fault-simulations.*', 'solar-systems.fault-simulations.*') ? 'active' : '' }}">
                  <a href="{{ route('fault-simulations.index') }}">{{ __('Fault Simulations') }}</a>
                </li>
                <!-- Theme Toggle -->
                <li class="menu-item">
                  <button id="themeToggle" class="nav-link btn btn-link" title="Toggle dark mode">
                    <i class="bi bi-moon-stars"></i>
                  </button>
                </li>
              
            </ul>
          </div>
        </div>
      </header>
    </div>

    <div class="container-fluid">
        <div class="row">
            <main class="col-12 main-content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeInUp" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeInUp" role="alert">
                        <i class="bi bi-exclamation-circle-fill me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <footer class="footer mt-auto">
        <div class="container">
            <p class="mb-0">&copy; {{ date('Y') }} <span class="text-warning">SolarSmart</span> - Solar Energy Monitoring System</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @stack('scripts')

<script>
         document.addEventListener('DOMContentLoaded', function() {
             const menuToggle = document.getElementById('menu-toggle');
             const menuOpen = document.getElementById('menu-open');
             const menuClose = document.getElementById('menu-close');
             const menu = document.querySelector('.menu');

             if (menuToggle) {
                 menuToggle.addEventListener('click', function() {
                     menu.classList.toggle('show');
                     menuOpen.style.display = menu.classList.contains('show') ? 'none' : 'block';
                     menuClose.style.display = menu.classList.contains('show') ? 'block' : 'none';
                 });
             }

             const themeToggle = document.getElementById('themeToggle');
             const body = document.body;

             if (themeToggle) {
                 const savedTheme = localStorage.getItem('theme') || 'dark';
                 body.setAttribute('data-bs-theme', savedTheme);

                 const icon = themeToggle.querySelector('i');
                 if (savedTheme === 'dark') {
                     icon.classList.remove('bi-sun-fill');
                     icon.classList.add('bi-moon-stars');
                 } else {
                     icon.classList.remove('bi-moon-stars');
                     icon.classList.add('bi-sun-fill');
                 }

                 themeToggle.addEventListener('click', function(e) {
                     e.preventDefault();
                     const currentTheme = body.getAttribute('data-bs-theme');
                     const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                     body.setAttribute('data-bs-theme', newTheme);

                     if (newTheme === 'dark') {
                         icon.classList.remove('bi-sun-fill');
                         icon.classList.add('bi-moon-stars');
                     } else {
                         icon.classList.remove('bi-moon-stars');
                         icon.classList.add('bi-sun-fill');
                     }

                     localStorage.setItem('theme', newTheme);
                 });
             }
         });
     </script>
</body>
</html>

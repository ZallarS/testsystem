<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= \App\Core\CSRF::generateToken() ?>">
    <title><?= $title ?? 'My Application' ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
            --header-height: 64px;
            --primary-color: #2563eb;
            --secondary-color: #64748b;
            --background-color: #f8fafc;
            --border-color: #e2e8f0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--background-color);
            color: #1e293b;
            line-height: 1.6;
        }

        /* Layout */
        .app-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: white;
            border-right: 1px solid var(--border-color);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            transition: transform 0.3s ease;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            height: var(--header-height);
            display: flex;
            align-items: center;
        }

        .sidebar-brand {
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--primary-color);
            text-decoration: none;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-section {
            margin-bottom: 1.5rem;
        }

        .nav-section-title {
            padding: 0.5rem 1.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--secondary-color);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .nav-item {
            margin: 0.25rem 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: #475569;
            text-decoration: none;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }

        .nav-link:hover {
            background-color: #f1f5f9;
            color: var(--primary-color);
        }

        .nav-link.active {
            background-color: #eff6ff;
            color: var(--primary-color);
            border-left-color: var(--primary-color);
            font-weight: 500;
        }

        .nav-link i {
            width: 20px;
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        /* Header */
        .header {
            height: var(--header-height);
            background: white;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 900;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem;
            border-radius: 8px;
            transition: background-color 0.2s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            border: none;
            background: none;
        }

        .user-menu:hover {
            background-color: #f1f5f9;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .user-name {
            font-weight: 500;
            font-size: 0.875rem;
        }

        .user-role {
            font-size: 0.75rem;
            color: var(--secondary-color);
        }

        .dropdown-arrow {
            transition: transform 0.2s ease;
            color: var(--secondary-color);
        }

        .user-dropdown.show .dropdown-arrow {
            transform: rotate(180deg);
        }

        .user-dropdown .dropdown-menu {
            position: absolute;
            top: 100%;

            left: auto;
            margin-top: 0.5rem;
            z-index: 1001;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            transition: all 0.2s ease;
            text-decoration: none;
            color: #475569;
        }

        .dropdown-item:hover {
            background-color: #f1f5f9;
            color: var(--primary-color);
        }

        .dropdown-item i {
            width: 20px;
            margin-right: 0.75rem;
            font-size: 1rem;
        }

        .dropdown-divider {
            margin: 0.5rem 0;
            border-color: var(--border-color);
        }

        /* Content Area */
        .content-area {
            padding: 2rem;
            max-width: 2500px;

        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .page-description {
            color: var(--secondary-color);
            font-size: 1rem;
        }

        /* Mobile Toggle */
        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.25rem;
            color: var(--secondary-color);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 6px;
            transition: background-color 0.2s ease;
        }

        .mobile-toggle:hover {
            background-color: #f1f5f9;
        }

        /* Mobile Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        /* Cards */
        .card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }

        .card-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            background: white;
        }

        .card-title {
            font-weight: 600;
            font-size: 1.125rem;
            color: #1e293b;
            margin: 0;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Buttons */
        .btn {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.625rem 1.25rem;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        .btn-outline-primary {
            background: transparent;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }

        .btn-outline-primary:hover {
            background: var(--primary-color);
            color: white;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        /* Alerts */
        .alert {
            border-radius: 8px;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border-left: 4px solid #22c55e;
        }

        .alert-danger {
            background: #fef2f2;
            color: #dc2626;
            border-left: 4px solid #ef4444;
        }

        /* Hero Section */
        .hero-section {
            text-align: center;
            padding: 4rem 0;
        }

        .hero-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #1e293b;
        }

        .hero-description {
            font-size: 1.25rem;
            color: var(--secondary-color);
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* Features Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
        }

        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }

        .feature-card:hover {
            transform: translateY(-4px);
        }

        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.mobile-open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .mobile-toggle {
                display: block;
            }

            .sidebar-overlay.mobile-open {
                display: block;
            }

            .content-area {
                padding: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .user-dropdown .dropdown-menu {
                right: auto;
                right: 0;
                transform: translateX(0);
            }
            .header {
                padding: 0 1rem;
            }

            .content-area {
                padding: 1rem;
            }

            .user-info {
                display: none;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .hero-title {
                font-size: 2rem;
            }

            .hero-actions {
                flex-direction: column;
                align-items: center;
            }

            .hero-actions .btn {
                width: 100%;
                max-width: 300px;
            }
        }

        @media (max-width: 640px) {
            :root {
                --header-height: 56px;
            }

            .content-area {
                padding: 0.75rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Utilities */
        .text-truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .shadow-sm {
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
        }
    </style>
</head>
<body>
<div class="app-container">
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <?php if (\App\Core\User::isLoggedIn() && \App\Core\User::isAdmin()): ?>
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="/" class="sidebar-brand">
                    <i class="fas fa-cube me-2"></i>MyApp
                </a>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Администрирование</div>
                    <div class="nav-item">
                        <a class="nav-link <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>" href="/admin">
                            <i class="fas fa-chart-pie"></i>
                            <span>Дашборд</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a class="nav-link <?= ($activeMenu ?? '') === 'users' ? 'active' : '' ?>" href="/admin/users">
                            <i class="fas fa-users"></i>
                            <span>Пользователи</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a class="nav-link <?= ($activeMenu ?? '') === 'settings' ? 'active' : '' ?>" href="/admin/settings">
                            <i class="fas fa-cog"></i>
                            <span>Настройки</span>
                        </a>
                    </div>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Быстрые действия</div>
                    <div class="nav-item">
                        <a class="nav-link" href="/admin/users/create">
                            <i class="fas fa-user-plus"></i>
                            <span>Добавить пользователя</span>
                        </a>
                    </div>
                </div>
            </nav>
        </aside>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <header class="header">
            <button class="mobile-toggle" id="mobileToggle">
                <i class="fas fa-bars"></i>
            </button>

            <div class="header-actions">
                <?php if (\App\Core\User::isLoggedIn()): ?>
                    <!-- Выпадающее меню пользователя -->
                    <div class="user-dropdown" id="userDropdown">
                        <button class="user-menu" type="button" id="userMenuButton">
                            <div class="user-avatar">
                                <?= strtoupper(substr(\App\Core\User::getName() ?? 'U', 0, 1)) ?>
                            </div>
                            <div class="user-info">
                                    <span class="user-name text-truncate" style="max-width: 120px;">
                                        <?= \App\Core\Helpers::e(\App\Core\User::getName() ?? 'User') ?>
                                    </span>
                                <span class="user-role">
                                        <?= \App\Core\User::isAdmin() ? 'Администратор' : 'Пользователь' ?>
                                    </span>
                            </div>
                            <i class="fas fa-chevron-down dropdown-arrow"></i>
                        </button>

                        <div class="dropdown-menu" aria-labelledby="userMenuButton">
                            <a class="dropdown-item" href="/profile">
                                <i class="fas fa-user"></i>
                                <span>Профиль</span>
                            </a>
                            <?php if (\App\Core\User::isAdmin()): ?>
                                <a class="dropdown-item" href="/admin">
                                    <i class="fas fa-cog"></i>
                                    <span>Панель управления</span>
                                </a>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="/logout">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Выйти</span>
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Кнопки для неавторизованных пользователей -->
                    <div class="d-flex gap-2">
                        <a href="/login" class="btn btn-outline-primary btn-sm">Войти</a>
                        <a href="/register" class="btn btn-primary btn-sm">Регистрация</a>
                    </div>
                <?php endif; ?>
            </div>
        </header>

        <!-- Content -->
        <div class="content-area">
            <!-- Flash Messages -->
            <?php if (isset($_GET['message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= \App\Core\Helpers::e($_GET['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= \App\Core\Helpers::e($_GET['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Page Content -->
            <?php include $content; ?>
        </div>
    </main>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const mobileToggle = document.getElementById('mobileToggle');
        const userDropdown = document.getElementById('userDropdown');
        const userMenuButton = document.getElementById('userMenuButton');

        // Mobile sidebar toggle
        if (mobileToggle && sidebar) {
            mobileToggle.addEventListener('click', function() {
                sidebar.classList.toggle('mobile-open');
                sidebarOverlay.classList.toggle('mobile-open');
            });
        }

        // Close sidebar when overlay is clicked
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('mobile-open');
                sidebarOverlay.classList.remove('mobile-open');
            });
        }

        // Close sidebar when link is clicked (mobile)
        const sidebarLinks = document.querySelectorAll('.sidebar .nav-link');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 1024) {
                    sidebar.classList.remove('mobile-open');
                    sidebarOverlay.classList.remove('mobile-open');
                }
            });
        });

        // User dropdown functionality
        if (userMenuButton && userDropdown) {
            userMenuButton.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdown.classList.toggle('show');

                const dropdownMenu = userDropdown.querySelector('.dropdown-menu');
                if (userDropdown.classList.contains('show')) {
                    dropdownMenu.style.display = 'block';
                } else {
                    dropdownMenu.style.display = 'none';
                }
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!userDropdown.contains(e.target)) {
                    userDropdown.classList.remove('show');
                    const dropdownMenu = userDropdown.querySelector('.dropdown-menu');
                    dropdownMenu.style.display = 'none';
                }
            });

            // Close dropdown when clicking on dropdown items
            const dropdownItems = userDropdown.querySelectorAll('.dropdown-item');
            dropdownItems.forEach(item => {
                item.addEventListener('click', function() {
                    userDropdown.classList.remove('show');
                    const dropdownMenu = userDropdown.querySelector('.dropdown-menu');
                    dropdownMenu.style.display = 'none';
                });
            });
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                sidebar.classList.remove('mobile-open');
                sidebarOverlay.classList.remove('mobile-open');

                // Reset dropdown on desktop
                if (userDropdown) {
                    userDropdown.classList.remove('show');
                    const dropdownMenu = userDropdown.querySelector('.dropdown-menu');
                    if (dropdownMenu) {
                        dropdownMenu.style.display = 'none';
                    }
                }
            }
        });
    });
</script>
</body>
</html>
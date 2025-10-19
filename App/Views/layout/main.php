<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'My App' ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8f9fa;
            color: #333;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem 1rem;
            flex: 1;
        }

        /* Header */
        header {
            background: white;
            border-bottom: 1px solid #e1e5e9;
            padding: 1rem 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 800px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: #007bff;
            text-decoration: none;
        }

        .auth-buttons {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: 1px solid #007bff;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s ease;
            display: inline-block;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0056cc;
            transform: translateY(-1px);
        }

        .btn-outline {
            background: white;
            color: #007bff;
        }

        .btn-outline:hover {
            background: #007bff;
            color: white;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #007bff, #0056cc);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1rem;
        }

        /* Main Content */
        .card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
            border: 1px solid #e9ecef;
        }

        .hero {
            text-align: center;
            padding: 3rem 0;
        }

        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #212529;
            font-weight: 700;
        }

        .hero p {
            font-size: 1.2rem;
            color: #6c757d;
            margin-bottom: 2rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .info-item {
            text-align: center;
            padding: 1.5rem 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .info-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #007bff;
            margin-bottom: 0.5rem;
        }

        .info-label {
            font-size: 0.9rem;
            color: #6c757d;
            font-weight: 500;
        }

        h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #212529;
            margin-bottom: 1rem;
        }

        /* Profile Grid */
        .profile-grid {
            display: grid;
            gap: 0.75rem;
        }

        .profile-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e9ecef;
        }

        .profile-row:last-child {
            border-bottom: none;
        }

        .profile-label {
            color: #6c757d;
            font-weight: 500;
        }

        .profile-value {
            font-weight: 600;
            color: #212529;
        }

        /* Footer */
        footer {
            background: #212529;
            color: white;
            text-align: center;
            padding: 1.5rem;
            margin-top: auto;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav {
                flex-direction: column;
                gap: 1rem;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .info-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .container {
                padding: 1rem;
            }
        }

        @media (max-width: 480px) {
            .info-grid {
                grid-template-columns: 1fr;
            }

            .hero h1 {
                font-size: 1.75rem;
            }

            .user-info {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.2s ease;
            background: #fff;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .alert {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background: #d1edff;
            color: #0c5460;
            border: 1px solid #b8daff;
        }

        .auth-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 60vh;
            padding: 2rem 0;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8f9fa;
            color: #333;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
            flex: 1;
        }

        /* Header */
        header {
            background: white;
            border-bottom: 1px solid #e1e5e9;
            padding: 1rem 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: #007bff;
            text-decoration: none;
        }

        .main-nav {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-link {
            color: #6c757d;
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            color: #007bff;
            background: #f8f9fa;
        }

        .nav-link.active {
            color: #007bff;
            background: #e7f3ff;
        }

        .auth-buttons {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: 1px solid #007bff;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s ease;
            display: inline-block;
            cursor: pointer;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0056cc;
            transform: translateY(-1px);
        }

        .btn-outline {
            background: white;
            color: #007bff;
        }

        .btn-outline:hover {
            background: #007bff;
            color: white;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #007bff, #0056cc);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1rem;
        }

        .admin-badge {
            background: #28a745;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        /* Footer */
        footer {
            background: #212529;
            color: white;
            text-align: center;
            padding: 1.5rem;
            margin-top: auto;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav {
                flex-direction: column;
                gap: 1rem;
            }

            .main-nav {
                gap: 1rem;
            }

            .container {
                padding: 1rem;
            }
        }

        @media (max-width: 480px) {
            .user-info {
                flex-direction: column;
                gap: 0.5rem;
            }
        }

        .user-dropdown {
            position: relative;
            display: inline-block;
        }

        .user-dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: none;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            padding: 0.5rem 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
            background: white;
        }

        .user-dropdown-toggle:hover {
            border-color: #007bff;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.1);
        }

        .user-info-text {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 0.1rem;
        }

        .user-name {
            font-weight: 600;
            color: #212529;
            font-size: 0.9rem;
            line-height: 1;
        }

        .user-role {
            font-size: 0.75rem;
            color: #6c757d;
            line-height: 1;
        }

        .dropdown-arrow {
            color: #6c757d;
            font-size: 0.7rem;
            transition: transform 0.2s ease;
        }

        .user-dropdown.active .dropdown-arrow {
            transform: rotate(180deg);
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            min-width: 220px;
            margin-top: 0.5rem;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.2s ease;
            z-index: 1000;
        }

        .user-dropdown.active .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            text-decoration: none;
            color: #212529;
            transition: background-color 0.2s ease;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .dropdown-item:hover {
            background: #f8f9fa;
            color: #007bff;
            text-decoration: none;
        }

        .dropdown-item:first-child {
            border-radius: 8px 8px 0 0;
        }

        .dropdown-item:last-child {
            border-radius: 0 0 8px 8px;
        }

        .dropdown-icon {
            width: 20px;
            text-align: center;
            font-size: 1rem;
        }

        .dropdown-divider {
            height: 1px;
            background: #e9ecef;
            margin: 0.25rem 0;
        }

        .logout-item {
            color: #dc3545;
        }

        .logout-item:hover {
            background: #f8d7da;
            color: #dc3545;
        }

        /* Click outside to close */
        .dropdown-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 999;
            display: none;
        }

        .user-dropdown.active .dropdown-overlay {
            display: block;
        }
    </style>
</head>
<body>
<header>
    <div class="nav">
        <a href="/" class="logo">MyApp</a>

        <div class="main-nav"></div>

        <div class="auth-buttons">
            <?php if (\App\Core\User::isLoggedIn()): ?>
                <div class="user-dropdown">
                    <button class="user-dropdown-toggle">
                        <div class="user-avatar">
                            <?= e(strtoupper(substr(\App\Core\User::getName() ?? 'U', 0, 1))) ?>
                        </div>
                        <div class="user-info-text">
                            <span class="user-name"><?= e(\App\Core\User::getName() ?? 'Пользователь') ?></span>
                            <?php if (\App\Core\User::isAdmin()): ?>
                                <span class="user-role">Администратор</span>
                            <?php endif; ?>
                        </div>
                        <div class="dropdown-arrow">▼</div>
                    </button>

                    <div class="dropdown-menu">
                        <a href="/profile" class="dropdown-item">
                            <div class="dropdown-icon">👤</div>
                            <span>Личный кабинет</span>
                        </a>

                        <?php if (\App\Core\User::isAdmin()): ?>
                            <a href="/admin" class="dropdown-item">
                                <div class="dropdown-icon">📊</div>
                                <span>Админ-панель</span>
                            </a>
                        <?php endif; ?>

                        <div class="dropdown-divider"></div>

                        <a href="/logout" class="dropdown-item logout-item">
                            <div class="dropdown-icon">🚪</div>
                            <span>Выйти</span>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="/login" class="btn btn-outline">Войти</a>
                <a href="/register" class="btn btn-primary">Регистрация</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main class="container">
    <?= $content ?? '' ?>
</main>

<footer>
    <div class="container">
        <p>&copy; 2024 My Application. Все права защищены.</p>
    </div>
</footer>
</body>
</html>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const userDropdown = document.querySelector('.user-dropdown');
        const dropdownToggle = document.querySelector('.user-dropdown-toggle');
        const dropdownMenu = document.querySelector('.dropdown-menu');

        if (dropdownToggle) {
            // Toggle dropdown on click
            dropdownToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdown.classList.toggle('active');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!userDropdown.contains(e.target)) {
                    userDropdown.classList.remove('active');
                }
            });

            // Close dropdown when pressing Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    userDropdown.classList.remove('active');
                }
            });

            // Close dropdown after clicking on a menu item
            dropdownMenu.addEventListener('click', function(e) {
                if (e.target.tagName === 'A' || e.target.closest('a')) {
                    userDropdown.classList.remove('active');
                }
            });
        }

        // Prevent dropdown from closing when clicking inside it
        dropdownMenu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
</script>
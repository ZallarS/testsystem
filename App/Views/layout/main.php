<?php
use App\Core\User;

// Инициализируем сессию через класс User
User::initSession();

$isLoggedIn = User::isLoggedIn();
$userName = $isLoggedIn ? User::getName() : '';
$title = $title ?? 'Система тестирований';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <style>
        /* Общие стили */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Навигация */
        nav {
            background-color: #2c3e50;
            color: white;
            padding: 1rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .logo a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .nav-links {
            display: flex;
            align-items: center;
        }

        .nav-links ul {
            list-style-type: none;
            display: flex;
            align-items: center;
        }

        .nav-links li {
            margin-right: 1.5rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 0.5rem;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .nav-links a:hover {
            background-color: #34495e;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-info span {
            color: #ecf0f1;
        }

        /* Бургер-меню */
        .burger-menu {
            display: none;
            flex-direction: column;
            justify-content: space-around;
            width: 30px;
            height: 30px;
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0;
        }

        .burger-menu span {
            width: 100%;
            height: 3px;
            background-color: white;
            border-radius: 3px;
            transition: all 0.3s ease;
        }

        /* Мобильное меню */
        .mobile-menu {
            display: none;
            position: fixed;

            left: 0;
            right: 0;
            background-color: #2c3e50;
            padding: 1rem;
            box-shadow: 0 5px 10px rgba(0,0,0,0.2);
            z-index: 1000;
            max-height: calc(100vh - 70px);
            overflow-y: auto;
        }

        .mobile-menu.active {
            display: block;
        }

        .mobile-menu ul {
            list-style-type: none;
        }

        .mobile-menu li {
            margin-bottom: 1rem;
        }

        .mobile-menu a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 0.5rem;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .mobile-menu a:hover {
            background-color: #34495e;
        }

        .mobile-user-info {
            padding: 1rem 0;
            border-top: 1px solid #34495e;
            margin-top: 1rem;
        }

        .mobile-user-info span {
            color: #ecf0f1;
            display: block;
            margin-bottom: 1rem;
        }

        /* Основной контент */
        main {
            flex: 1;
            width: 100%;
            max-width: 1200px;
            margin: 80px auto 0; /* Отступ сверху равен высоте хедера */
            padding: 2rem 1rem;
        }

        /* Футер */
        footer {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 1.5rem;
            margin-top: auto;
        }

        footer p {
            margin: 0;
        }

        /* Контейнеры */
        .container {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        /* Адаптивность */
        @media (max-width: 768px) {
            .burger-menu {
                display: flex;
            }

            .nav-links {
                display: none;
            }

            .mobile-menu {
                display: none;
            }

            .mobile-menu.active {
                display: block;
            }

            /* Увеличим отступ для мобильных, если нужно */
            main {
                margin-top: 70px;
            }
        }
        /* Убедимся, что все интерактивные элементы не перекрываются хедером */
        .anchor {
            scroll-margin-top: 80px; /* Для якорных ссылок */
        }

        /* Для модальных окон и выпадающих меню */
        .modal, .dropdown {
            z-index: 1001; /* Выше чем хедер */
        }
    </style>
</head>
<body>
<nav>
    <div class="nav-container">
        <div class="logo">
            <a href="/">Система тестирований</a>
        </div>

        <div class="nav-links">
            <ul>
                <li>
                    <a href="/">Главная</a>
                    <?php if ($isLoggedIn): ?>
                        <a href="/admin">Панель администратора</a>
                        <a href="/admin/plugins">Плагины</a>
                    <?php endif; ?>
                </li>
            </ul>

            <div class="user-info">
                <?php if ($isLoggedIn): ?>
                    <span><?= htmlspecialchars($userName) ?></span>
                    <a href="/logout">Выйти</a>
                <?php else: ?>
                    <a href="/login">Авторизация</a>
                    <a href="/register">Регистрация</a>
                <?php endif; ?>
            </div>
        </div>

        <button class="burger-menu" id="burgerMenu">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>

    <div class="mobile-menu" id="mobileMenu">
        <ul>
            <li><a href="/">Главная</a></li>
            <?php if ($isLoggedIn): ?>
                <li><a href="/admin">Панель администратора</a></li>
                <li><a href="/admin/plugins">Плагины</a></li>
            <?php endif; ?>
        </ul>

        <div class="mobile-user-info">
            <?php if ($isLoggedIn): ?>
                <span>Welcome, <?= htmlspecialchars($userName) ?></span>
                <a href="/logout">Выйти</a>
            <?php else: ?>
                <a href="/login">Авторизация</a>
                <a href="/register">Регистрация</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main>
    <?php include $content; ?>
</main>

<footer>
    <p>&copy; <?= date('Y') ?> Система тестирований. Все права защищены.</p>
</footer>

<script>
    // JavaScript для управления мобильным меню
    document.addEventListener('DOMContentLoaded', function() {
        const burgerMenu = document.getElementById('burgerMenu');
        const mobileMenu = document.getElementById('mobileMenu');

        burgerMenu.addEventListener('click', function() {
            mobileMenu.classList.toggle('active');

            // Анимация бургер-меню
            const spans = burgerMenu.querySelectorAll('span');
            if (mobileMenu.classList.contains('active')) {
                spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
                spans[1].style.opacity = '0';
                spans[2].style.transform = 'rotate(-45deg) translate(7px, -6px)';
            } else {
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
            }
        });

        // Закрытие меню при клике вне его области
        document.addEventListener('click', function(event) {
            if (!event.target.closest('nav') && mobileMenu.classList.contains('active')) {
                mobileMenu.classList.remove('active');

                // Сброс анимации бургер-меню
                const spans = burgerMenu.querySelectorAll('span');
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
            }
        });

        // Закрытие меню при изменении размера окна
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768 && mobileMenu.classList.contains('active')) {
                mobileMenu.classList.remove('active');

                // Сброс анимации бургер-меню
                const spans = burgerMenu.querySelectorAll('span');
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
            }
        });
    });

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                const headerOffset = 80;
                const elementPosition = target.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
</script>
</body>
</html>
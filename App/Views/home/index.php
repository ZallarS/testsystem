<?php
use App\Core\User;
?>

<div class="home-container">
    <!-- Герой-секция -->
    <section class="hero-section">
        <div class="hero-content">
            <h1>Добро пожаловать в систему тестирований</h1>

            <?php if (User::isLoggedIn()): ?>
                <div class="hero-buttons">
                    <a href="/admin" class="btn btn-primary">Панель управления</a>
                    <a href="/logout" class="btn btn-outline">Выйти</a>
                </div>
            <?php else: ?>
                <div class="hero-buttons">
                    <a href="/login" class="btn btn-primary">Войти</a>
                    <a href="/register" class="btn btn-outline">Создать аккаунт</a>
                </div>
            <?php endif; ?>
        </div>
        <div class="hero-visual">
            <div class="visual-element"></div>
        </div>
    </section>

    <!-- Информация о статусе -->
    <section class="status-section">
        <?php if (User::isLoggedIn()): ?>
            <div class="alert alert-success">
                <div class="alert-content">
                    <h3>С возвращением, <?= htmlspecialchars(User::getName()) ?>!</h3>
                    <p>Вы вошли как <?= htmlspecialchars(User::getEmail()) ?></p>
                    <div class="alert-actions">
                        <a href="/admin" class="btn btn-sm btn-primary">Панель управления</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <!-- Особенности -->
    <section class="features-section">
        <h2 class="section-title">Что у нас есть?</h2>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" width="48" height="48">
                        <path fill="currentColor" d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M11,17L7,13L8.41,11.59L11,14.17L15.59,9.58L17,11L11,17Z" />
                    </svg>
                </div>
                <h3>Аутентификация пользователя</h3>
                <p>Защищенная система входа и регистрации с управлением сеансами и профилями пользователей.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" width="48" height="48">
                        <path fill="currentColor" d="M17,19H7V5H17M17,1H7C5.89,1 5,1.89 5,3V21A2,2 0 0,0 7,23H17A2,2 0 0,0 19,21V3C19,1.89 18.1,1 17,1M12,18A1,1 0 0,1 13,19A1,1 0 0,1 12,20A1,1 0 0,1 11,19A1,1 0 0,1 12,18Z" />
                    </svg>
                </div>
                <h3>Простой дизайн</h3>
                <p>Минималистичный интерфейс, который отлично работает на всех устройствах - настольных компьютерах, планшетах и смартфонах.</p>
            </div>
        </div>
    </section>
</div>

<style>
    /* Стили для главной страницы */
    .home-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1rem;
    }

    /* Герой-секция */
    .hero-section {
        display: flex;
        align-items: center;
        gap: 2rem;
        padding: 2rem 0;
        margin-bottom: 2rem;
    }

    .hero-content {
        flex: 1;
        min-width: 0; /* Предотвращает переполнение контента */
    }

    .hero-content h1 {
        font-size: 2.5rem;
        color: #2c3e50;
        margin-bottom: 1rem;
        line-height: 1.2;
        word-wrap: break-word;
    }

    .hero-subtitle {
        font-size: 1.125rem;
        color: #7f8c8d;
        margin-bottom: 1.5rem;
        word-wrap: break-word;
    }

    .hero-buttons {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .hero-visual {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        min-width: 0; /* Предотвращает переполнение контента */
    }

    .visual-element {
        width: 250px;
        height: 200px;
        background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
        border-radius: 15px;
        position: relative;
        box-shadow: 0 15px 30px rgba(52, 152, 219, 0.2);
        flex-shrink: 0; /* Предотвращает сжатие элемента */
    }

    /* Секция статуса */
    .status-section {
        margin-bottom: 2rem;
    }

    .alert-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .alert-content h3 {
        margin-bottom: 0.5rem;
        flex: 1 1 100%;
        word-wrap: break-word;
    }

    .alert-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    /* Секция особенностей */
    .features-section {
        padding: 2rem 0;
        margin-bottom: 2rem;
        background-color: #f8f9fa;
        border-radius: 12px;
    }

    .section-title {
        text-align: center;
        font-size: 2rem;
        color: #2c3e50;
        margin-bottom: 1rem;
        padding: 0 1rem;
        word-wrap: break-word;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        padding: 0 1rem;
    }

    .feature-card {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
        text-align: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        display: flex;
        flex-direction: column;
        min-height: 300px; /* Фиксированная минимальная высота */
        word-wrap: break-word;
        overflow: hidden; /* Предотвращает выход контента за пределы */
    }

    .feature-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
    }

    .feature-icon {
        margin-bottom: 1rem;
        color: #3498db;
        flex-shrink: 0; /* Предотвращает сжатие иконки */
    }

    .feature-card h3 {
        font-size: 1.125rem;
        color: #2c3e50;
        margin-bottom: 0.75rem;
        word-wrap: break-word;
        line-height: 1.3;
    }

    .feature-card p {
        color: #7f8c8d;
        line-height: 1.5;
        font-size: 0.95rem;
        word-wrap: break-word;
        flex-grow: 1; /* Заставляет параграф занимать доступное пространство */
        overflow: hidden; /* Скрывает переполнение */
        display: -webkit-box;
        -webkit-line-clamp: 4; /* Ограничивает количество строк */
        -webkit-box-orient: vertical;
    }

    /* Кнопки */
    .btn {
        display: inline-block;
        padding: 0.75rem 1.5rem;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        text-align: center;
        white-space: nowrap;
    }

    .btn-primary {
        background-color: #3498db;
        color: white;
    }

    .btn-primary:hover {
        background-color: #2980b9;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
    }

    .btn-outline {
        background-color: transparent;
        color: #3498db;
        border-color: #3498db;
    }

    .btn-outline:hover {
        background-color: #3498db;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
    }

    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }

    /* Адаптивность для мобильных устройств */
    @media (max-width: 768px) {
        .home-container {
            padding: 0 0.5rem;
        }

        .hero-section {
            flex-direction: column;
            text-align: center;
            gap: 1.5rem;
            padding: 1.5rem 0;
        }

        .hero-content h1 {
            font-size: 2rem;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 1rem;
            line-height: 1.4;
        }

        .hero-buttons {
            justify-content: center;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        .hero-buttons .btn {
            width: 100%;
            max-width: 250px;
            margin-bottom: 0.5rem;
        }

        .visual-element {
            width: 200px;
            height: 160px;
        }

        .alert-content {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }

        .alert-actions {
            justify-content: center;
            width: 100%;
        }

        .alert-actions .btn {
            flex: 1;
            min-width: 120px;
            max-width: 200px;
        }

        .features-section {
            padding: 1.5rem 0;
            margin: 0 0 2rem;
            border-radius: 0;
        }

        .section-title {
            font-size: 1.75rem;
            padding: 0 0.5rem;
            line-height: 1.3;
        }

        .features-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
            padding: 0 0.5rem;
        }

        .feature-card {
            padding: 1.25rem;
            min-height: auto; /* Убираем фиксированную высоту на мобильных */
            margin: 0 auto;
            max-width: 100%;
            width: 100%;
            box-sizing: border-box;
        }

        .feature-card h3 {
            font-size: 1.1rem;
            line-height: 1.3;
        }

        .feature-card p {
            font-size: 0.9rem;
            line-height: 1.4;
            -webkit-line-clamp: unset; /* Убираем ограничение строк на мобильных */
        }
    }

    /* Адаптивность для очень маленьких устройств */
    @media (max-width: 480px) {
        .hero-content h1 {
            font-size: 1.75rem;
        }

        .hero-subtitle {
            font-size: 0.95rem;
        }

        .visual-element {
            width: 160px;
            height: 130px;
        }

        .section-title {
            font-size: 1.5rem;
        }

        .btn {
            padding: 0.625rem 1.25rem;
            font-size: 0.9rem;
        }

        .feature-card {
            padding: 1rem;
        }

        .feature-icon {
            width: 40px;
            height: 40px;
        }
    }
</style>
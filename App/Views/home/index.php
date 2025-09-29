<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyApp - Главная страница</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background-color: #ffffff;
            color: #2c3e50;
            line-height: 1.6;
        }

        .minimal-homepage {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Навбар */
        .navbar {
            background: white;
            border-bottom: 1px solid #e9ecef;
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .navbar-brand i {
            margin-right: 0.5rem;
            color: #3498db;
        }

        .navbar-controls {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-1px);
        }

        .btn-outline {
            border: 2px solid #3498db;
            color: #3498db;
            background: transparent;
        }

        .btn-outline:hover {
            background: #3498db;
            color: white;
        }

        /* Герой секция */
        .hero-section {

            display: flex;
            align-items: center;
            padding: 4rem 0;
        }

        .hero-content {
            flex: 1;
            padding-right: 2rem;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero-title .accent {
            color: #3498db;
        }

        .hero-description {
            font-size: 1.25rem;
            color: #6c757d;
            margin-bottom: 2rem;
            max-width: 600px;
        }

        .hero-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .hero-image {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .placeholder-graphic {
            font-size: 12rem;
            color: #3498db;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }

        /* Преимущества */
        .features-section {
            padding: 4rem 0;
            background: #f8f9fa;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .section-subtitle {
            text-align: center;
            color: #6c757d;
            font-size: 1.25rem;
            margin-bottom: 3rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            font-size: 3rem;
            color: #3498db;
            margin-bottom: 1rem;
        }

        .feature-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .feature-description {
            color: #6c757d;
        }

        /* CTA секция */
        .cta-section {
            padding: 4rem 0;
            text-align: center;
        }

        .cta-content {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 4rem 2rem;
            border-radius: 12px;
        }

        .cta-title {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .cta-description {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .cta-button {
            background: white;
            color: #3498db;
            padding: 0.75rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .cta-button:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
        }

        /* Футер */
        .footer {
            background: #2c3e50;
            color: white;
            padding: 2rem 0;
            text-align: center;
        }

        /* Анимации */
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }

        /* Адаптивность */
        @media (max-width: 768px) {
            .hero-section {
                flex-direction: column;
                text-align: center;
                min-height: auto;
                padding: 2rem 0;
            }

            .hero-content {
                padding-right: 0;
                margin-bottom: 2rem;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .hero-actions {
                justify-content: center;
            }

            .placeholder-graphic {
                font-size: 8rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .navbar-container {
                flex-direction: column;
                gap: 1rem;
            }

            .navbar-controls {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .hero-title {
                font-size: 2rem;
            }

            .hero-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .placeholder-graphic {
                font-size: 6rem;
            }
        }
    </style>
</head>
<body>
<!-- Навбар -->
<nav class="navbar">
    <div class="navbar-container">
        <a class="navbar-brand" href="/">
            <i class="fas fa-cube"></i>MyApp
        </a>
        <div class="navbar-controls">
            <a href="/login" class="btn btn-outline">Войти</a>
            <a href="/register" class="btn btn-primary">Регистрация</a>
        </div>
    </div>
</nav>

<!-- Главный контент -->
<div class="minimal-homepage">
    <!-- Герой секция -->
    <section class="hero-section">
        <?php if (!\App\Core\User::isLoggedIn()): ?>
            <!-- Для неавторизованных пользователей -->
            <div class="hero-section">
                <div class="container">
                    <h1 class="hero-title">Добро пожаловать в MyApp</h1>
                    <p class="hero-description">
                        Простая и эффективная система для управления вашими задачами.
                        Создавайте, организуйте и отслеживайте прогресс в одном месте.
                    </p>
                    <div class="hero-actions">
                        <a href="/register" class="btn btn-primary btn-lg">
                            <i class="fas fa-rocket me-2"></i>Начать сейчас
                        </a>
                        <a href="/login" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Войти
                        </a>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- Для авторизованных пользователей -->
            <div class="hero-section">
                <div class="container">
                    <h1 class="hero-title">Добро пожаловать, <?= \App\Core\Helpers::e(\App\Core\User::getName()) ?>!</h1>
                    <p class="hero-description">
                        Рады видеть вас снова в системе MyApp.
                    </p>
                    <div class="hero-actions">
                        <?php if (\App\Core\User::isAdmin()): ?>
                            <!-- Для администраторов -->
                            <a href="/admin" class="btn btn-primary btn-lg">
                                <i class="fas fa-cog me-2"></i>Панель управления
                            </a>
                            <a href="/admin/users" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-users me-2"></i>Управление пользователями
                            </a>
                        <?php else: ?>
                            <!-- Для обычных пользователей -->
                            <a href="/profile" class="btn btn-primary btn-lg">
                                <i class="fas fa-user me-2"></i>Личный кабинет
                            </a>
                            <a href="/tasks" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-tasks me-2"></i>Мои задачи
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Быстрый доступ</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <?php if (\App\Core\User::isAdmin()): ?>
                                    <a href="/admin/users/create" class="btn btn-outline-primary">
                                        <i class="fas fa-user-plus me-2"></i>Добавить пользователя
                                    </a>
                                    <a href="/admin/settings" class="btn btn-outline-primary">
                                        <i class="fas fa-cog me-2"></i>Настройки системы
                                    </a>
                                <?php else: ?>
                                    <a href="/profile/edit" class="btn btn-outline-primary">
                                        <i class="fas fa-edit me-2"></i>Редактировать профиль
                                    </a>
                                    <a href="/tasks/create" class="btn btn-outline-primary">
                                        <i class="fas fa-plus me-2"></i>Создать задачу
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Статистика</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <?php if (\App\Core\User::isAdmin()): ?>
                                    <div class="col-6">
                                        <h4 class="text-primary">150</h4>
                                        <small class="text-muted">Пользователей</small>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="text-primary">12</h4>
                                        <small class="text-muted">Администраторов</small>
                                    </div>
                                <?php else: ?>
                                    <div class="col-6">
                                        <h4 class="text-primary">5</h4>
                                        <small class="text-muted">Активных задач</small>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="text-primary">23</h4>
                                        <small class="text-muted">Завершенных задач</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Последние действия -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Последние действия</h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-sign-in-alt text-success me-2"></i>
                                        <span>Вход в систему</span>
                                    </div>
                                    <small class="text-muted">Только что</small>
                                </div>
                                <?php if (\App\Core\User::isAdmin()): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-user-plus text-primary me-2"></i>
                                            <span>Добавлен новый пользователь</span>
                                        </div>
                                        <small class="text-muted">2 часа назад</small>
                                    </div>
                                <?php else: ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-tasks text-primary me-2"></i>
                                            <span>Задача "Обновить профиль" завершена</span>
                                        </div>
                                        <small class="text-muted">Вчера</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <!-- Преимущества -->
    <section class="features-section">
        <h2 class="section-title">Почему выбирают нас</h2>
        <p class="section-subtitle">Простота, надежность и эффективность</p>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <h3 class="feature-title">Быстро</h3>
                <p class="feature-description">
                    Мгновенный доступ к вашим данным с любого устройства в любое время
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 class="feature-title">Безопасно</h3>
                <p class="feature-description">
                    Ваши данные защищены современными методами шифрования и безопасности
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-infinity"></i>
                </div>
                <h3 class="feature-title">Просто</h3>
                <p class="feature-description">
                    Интуитивно понятный интерфейс без лишних сложностей и обучения
                </p>
            </div>
        </div>
    </section>

    <!-- CTA секция -->
    <section class="cta-section">
        <div class="cta-content">
            <h2 class="cta-title">Готовы начать?</h2>
            <p class="cta-description">
                Присоединяйтесь к тысячам довольных пользователей по всему миру
            </p>
            <a href="/register" class="btn cta-button">
                <i class="fas fa-user-plus me-2"></i>Создать аккаунт
            </a>
        </div>
    </section>
</div>

<!-- Футер -->
<footer class="footer">
    <div class="minimal-homepage">
        <p>&copy; 2024 MyApp. Все права защищены.</p>
    </div>
</footer>

<script>
    // Простой JavaScript для интерактивности
    document.addEventListener('DOMContentLoaded', function() {
        // Добавляем анимацию при скролле
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Анимируем карточки преимуществ
        const featureCards = document.querySelectorAll('.feature-card');
        featureCards.forEach(function(card, index) {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            card.style.transitionDelay = (index * 0.1) + 's';
            observer.observe(card);
        });

        // Плавная прокрутка для якорей
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    });
</script>
</body>
</html>

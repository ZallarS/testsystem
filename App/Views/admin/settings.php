<?php
// App/Views/admin/settings.php
?>

<div class="row">
    <div class="admin-header">
        <h1>Настройки системы</h1>
        <p>Управление конфигурацией приложения</p>
    </div>

    <div class="settings-grid">
        <div class="settings-section">
            <h2>Основные настройки</h2>

            <form method="POST" action="/admin/settings">
                <?= \App\Core\Helpers::csrfField() ?>

                <div class="form-group">
                    <label for="site_name">Название сайта</label>
                    <input type="text" id="site_name" name="site_name" value="My Application"
                           placeholder="Введите название сайта">
                </div>

                <div class="form-group">
                    <label for="site_email">Email администратора</label>
                    <input type="email" id="site_email" name="site_email" value="admin@example.com"
                           placeholder="admin@example.com">
                </div>

                <div class="form-group">
                    <label for="timezone">Часовой пояс</label>
                    <select id="timezone" name="timezone">
                        <option value="Europe/Moscow">Europe/Moscow (Москва)</option>
                        <option value="UTC">UTC</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Сохранить настройки</button>
            </form>
        </div>

        <div class="settings-section">
            <h2>Безопасность</h2>

            <div class="security-actions">
                <div class="security-item">
                    <h4>Требования к паролю</h4>
                    <p>Минимальная длина: 8 символов</p>
                    <button class="btn btn-outline">Изменить</button>
                </div>

                <div class="security-item">
                    <h4>Сессии</h4>
                    <p>Время жизни сессии: 24 часа</p>
                    <button class="btn btn-outline">Настроить</button>
                </div>

                <div class="security-item">
                    <h4>Резервные копии</h4>
                    <p>Последняя копия: Сегодня, 08:00</p>
                    <button class="btn btn-outline">Создать копию</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .settings-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
    }

    .settings-section {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: 1px solid #e9ecef;
    }

    .settings-section h2 {
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
        color: #212529;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #007bff;
    }

    .security-actions {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .security-item {
        padding: 1rem;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        background: #f8f9fa;
    }

    .security-item h4 {
        margin: 0 0 0.5rem 0;
        color: #212529;
    }

    .security-item p {
        margin: 0 0 1rem 0;
        color: #6c757d;
        font-size: 0.9rem;
    }

    @media (max-width: 768px) {
        .settings-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
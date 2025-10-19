<?php
// App/Views/admin/dashboard.php
?>

<div class="admin-header">
    <h1>Административная панель</h1>
    <p>Управление системой и пользователями</p>
</div>

<div class="admin-dashboard">

    <div class="actions-section">
        <h2>Быстрые действия</h2>
        <div class="actions-grid">
            <a href="/admin/users" class="action-card">
                <div class="action-icon">👥</div>
                <div class="action-content">
                    <h3>Управление пользователями</h3>
                    <p>Просмотр, редактирование и удаление пользователей</p>
                </div>
                <div class="action-arrow">→</div>
            </a>

            <a href="/admin/settings" class="action-card">
                <div class="action-icon">⚙️</div>
                <div class="action-content">
                    <h3>Настройки системы</h3>
                    <p>Конфигурация приложения и параметры</p>
                </div>
                <div class="action-arrow">→</div>
            </a>

            <div class="action-card" onclick="alert('Функция в разработке')">
                <div class="action-icon">📈</div>
                <div class="action-content">
                    <h3>Статистика</h3>
                    <p>Аналитика и отчеты системы</p>
                </div>
                <div class="action-arrow">→</div>
            </div>

            <div class="action-card" onclick="alert('Функция в разработке')">
                <div class="action-icon">🛡️</div>
                <div class="action-content">
                    <h3>Безопасность</h3>
                    <p>Настройки безопасности и доступов</p>
                </div>
                <div class="action-arrow">→</div>
            </div>
        </div>
    </div>

</div>

<style>
    .admin-header {
        text-align: center;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e9ecef;
        max-width: 1000px;
        margin-left: auto;
        margin-right: auto;
    }

    .admin-header h1 {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
        color: #212529;
    }

    .admin-header p {
        color: #6c757d;
        font-size: 1.1rem;
    }

    .admin-dashboard {
        max-width: 1000px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 2rem;
        width: 100%;
    }

    /* Sections */
    .stats-section,
    .actions-section,
    .recent-section {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: 1px solid #e9ecef;
        width: 100%;
        box-sizing: border-box;
    }

    .stats-section h2,
    .actions-section h2,
    .recent-section h2 {
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
        color: #212529;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #007bff;
    }

    /* Statistics Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        width: 100%;
    }

    .stat-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1.5rem;
        border: 1px solid #e9ecef;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.2s ease;
        width: 100%;
        box-sizing: border-box;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border-color: #007bff;
    }

    .stat-icon {
        font-size: 2rem;
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        flex-shrink: 0;
    }

    .stat-content {
        flex: 1;
        min-width: 0;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #007bff;
        line-height: 1;
        margin-bottom: 0.25rem;
    }

    .stat-label {
        color: #6c757d;
        font-weight: 500;
        font-size: 0.9rem;
    }

    /* Actions Grid */
    .actions-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
        width: 100%;
    }

    .action-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1.5rem;
        border: 1px solid #e9ecef;
        display: flex;
        align-items: center;
        gap: 1rem;
        text-decoration: none;
        color: inherit;
        transition: all 0.2s ease;
        cursor: pointer;
        width: 100%;
        box-sizing: border-box;
    }

    .action-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border-color: #007bff;
        text-decoration: none;
        color: inherit;
    }

    .action-icon {
        font-size: 1.5rem;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        flex-shrink: 0;
    }

    .action-content {
        flex: 1;
        min-width: 0;
    }

    .action-content h3 {
        font-size: 1.2rem;
        margin-bottom: 0.5rem;
        color: #212529;
    }

    .action-content p {
        color: #6c757d;
        margin: 0;
        font-size: 0.9rem;
        line-height: 1.4;
    }

    .action-arrow {
        color: #6c757d;
        font-size: 1.2rem;
        font-weight: bold;
        flex-shrink: 0;
    }

    /* Recent Activity */
    .recent-content {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        width: 100%;
    }

    .activity-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        background: #f8f9fa;
        width: 100%;
        box-sizing: border-box;
    }

    .activity-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        border-radius: 50%;
        font-size: 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        flex-shrink: 0;
    }

    .activity-content {
        flex: 1;
        min-width: 0;
    }

    .activity-content p {
        margin: 0 0 0.25rem 0;
        color: #212529;
        font-weight: 500;
    }

    .activity-time {
        font-size: 0.8rem;
        color: #6c757d;
    }

    /* Responsive Design */
    @media (max-width: 1100px) {
        .admin-dashboard {
            max-width: 900px;
        }
    }

    @media (max-width: 968px) {
        .admin-dashboard {
            max-width: 700px;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .actions-grid {
            grid-template-columns: 1fr;
        }

        .admin-header h1 {
            font-size: 2rem;
        }
    }

    @media (max-width: 768px) {
        .admin-dashboard {
            max-width: 100%;
            padding: 0 1rem;
            gap: 1.5rem;
        }

        .stats-section,
        .actions-section,
        .recent-section {
            padding: 1rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .stat-card {
            padding: 1rem;
        }

        .action-card {
            padding: 1rem;
        }

        .activity-item {
            padding: 0.75rem;
        }
    }

    @media (max-width: 480px) {
        .admin-header h1 {
            font-size: 1.75rem;
        }

        .stats-section h2,
        .actions-section h2,
        .recent-section h2 {
            font-size: 1.25rem;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
        }

        .stat-number {
            font-size: 1.5rem;
        }

        .action-icon {
            width: 40px;
            height: 40px;
            font-size: 1.2rem;
        }

        .action-content h3 {
            font-size: 1.1rem;
        }

        .activity-item {
            flex-direction: column;
            text-align: center;
            gap: 0.75rem;
        }

        .activity-content {
            text-align: center;
        }
    }
</style>
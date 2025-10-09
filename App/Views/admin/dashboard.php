<?php
// App/Views/admin/dashboard.php
?>

<div class="row">
    <div class="admin-header">
        <h1>Административная панель</h1>
        <p>Управление системой и пользователями</p>
    </div>

    <div class="admin-stats-grid">

            <a href="/admin/settings" class="action-card">
                <div class="action-icon">⚙️</div>
                <div class="action-content">
                    <h3>Настройки системы</h3>
                    <p>Конфигурация приложения и параметры</p>
                </div>
            </a>

            <a href="/admin/users" class="action-card">
                <div class="action-icon">👥</div>
                <div class="action-content">
                    <h3>Управление пользователями</h3>
                    <p>Просмотр, редактирование и удаление пользователей</p>
                </div>
            </a>

    </div>

</div>

<style>
    .admin-header {
        text-align: center;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e9ecef;
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

    /* Statistics Grid */
    .admin-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: 1px solid #e9ecef;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .stat-icon {
        font-size: 2rem;
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        border-radius: 50%;
    }

    .stat-content {
        flex: 1;
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
    }

    /* Actions Grid */
    .admin-actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .action-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: 1px solid #e9ecef;
        display: flex;
        align-items: center;
        gap: 1rem;
        text-decoration: none;
        color: inherit;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .action-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        text-decoration: none;
        color: inherit;
        border-color: #007bff;
    }

    .action-icon {
        font-size: 1.5rem;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        border-radius: 50%;
    }

    .action-content {
        flex: 1;
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
    }

    .action-arrow {
        color: #6c757d;
        font-size: 1.2rem;
        font-weight: bold;
    }

    /* Recent Activity */
    .recent-activity {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: 1px solid #e9ecef;
    }

    .recent-activity h2 {
        font-size: 1.5rem;
        margin-bottom: 1rem;
        color: #212529;
    }

    .activity-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .activity-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f8f9fa;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        border-radius: 50%;
        font-size: 1rem;
    }

    .activity-content {
        flex: 1;
    }

    .activity-content p {
        margin: 0;
        color: #212529;
    }

    .activity-time {
        font-size: 0.8rem;
        color: #6c757d;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .admin-stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .admin-actions-grid {
            grid-template-columns: 1fr;
        }

        .admin-header h1 {
            font-size: 2rem;
        }
    }

    @media (max-width: 480px) {
        .admin-stats-grid {
            grid-template-columns: 1fr;
        }

        .stat-card {
            padding: 1rem;
        }

        .action-card {
            padding: 1rem;
        }
    }
</style>
<?php
// App/Views/profile/index.php
?>

<div class="profile-header">
    <h1>Личный кабинет</h1>
    <p>Управление вашей учетной записью</p>
</div>

<div class="profile-layout">
    <div class="profile-sidebar">
        <div class="profile-card">
            <div class="profile-avatar-large">
                <?= strtoupper(substr(\App\Core\User::getName() ?? 'U', 0, 1)) ?>
            </div>
            <div class="profile-info">
                <h3><?= htmlspecialchars(\App\Core\User::getName() ?? 'Пользователь') ?></h3>
                <p class="profile-email"><?= htmlspecialchars(\App\Core\User::getEmail() ?? '') ?></p>
                <div class="profile-badges">
                    <?php if (\App\Core\User::isAdmin()): ?>
                        <span class="badge admin">Администратор</span>
                    <?php else: ?>
                        <span class="badge user">Пользователь</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="profile-stats">
            <div class="stat-item">
                <div class="stat-number"><?= date('d.m.Y') ?></div>
                <div class="stat-label">Дата регистрации</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">Активен</div>
                <div class="stat-label">Статус</div>
            </div>
        </div>
    </div>

    <div class="profile-content">
        <div class="profile-section">
            <h2>Основная информация</h2>
            <div class="info-grid">
                <div class="info-item">
                    <label>Имя пользователя</label>
                    <div class="info-value"><?= htmlspecialchars(\App\Core\User::getName() ?? 'Не указано') ?></div>
                </div>
                <div class="info-item">
                    <label>Email</label>
                    <div class="info-value"><?= htmlspecialchars(\App\Core\User::getEmail() ?? 'Не указан') ?></div>
                </div>
                <div class="info-item">
                    <label>ID пользователя</label>
                    <div class="info-value">#<?= \App\Core\User::getId() ?? 'Неизвестно' ?></div>
                </div>
                <div class="info-item">
                    <label>Роли</label>
                    <div class="info-value">
                        <?php
                        $roles = \App\Core\User::getRoles();
                        if (!empty($roles)):
                            foreach ($roles as $role):
                                ?>
                                <span class="role-tag"><?= htmlspecialchars($role) ?></span>
                            <?php
                            endforeach;
                        else:
                            ?>
                            <span class="role-tag">user</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    .profile-header {
        text-align: center;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e9ecef;
        max-width: 1000px;
        margin-left: auto;
        margin-right: auto;
    }

    .profile-header h1 {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
        color: #212529;
    }

    .profile-header p {
        color: #6c757d;
        font-size: 1.1rem;
    }

    .profile-layout {
        display: grid;
        grid-template-columns: 320px 1fr;
        gap: 2rem;
        max-width: 1000px;
        margin: 0 auto;
        width: 100%;
    }

    /* Sidebar */
    .profile-sidebar {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        width: 320px;
        flex-shrink: 0;
    }

    .profile-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: 1px solid #e9ecef;
        text-align: center;
        width: 100%;
        box-sizing: border-box;
    }

    .profile-avatar-large {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #007bff, #0056cc);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 2rem;
        margin: 0 auto 1rem;
    }

    .profile-info h3 {
        margin: 0 0 0.5rem 0;
        color: #212529;
        font-size: 1.2rem;
    }

    .profile-email {
        color: #6c757d;
        margin: 0 0 1rem 0;
        font-size: 0.9rem;
    }

    .profile-badges {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
    }

    .badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge.admin {
        background: #28a745;
        color: white;
    }

    .badge.user {
        background: #6c757d;
        color: white;
    }

    .profile-stats {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: 1px solid #e9ecef;
        width: 100%;
        box-sizing: border-box;
    }

    .stat-item {
        text-align: center;
        padding: 1rem 0;
        border-bottom: 1px solid #f8f9fa;
    }

    .stat-item:last-child {
        border-bottom: none;
    }

    .stat-number {
        font-size: 1.25rem;
        font-weight: 700;
        color: #007bff;
        margin-bottom: 0.25rem;
    }

    .stat-label {
        color: #6c757d;
        font-size: 0.9rem;
    }

    /* Content */
    .profile-content {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        min-width: 0; /* Prevent flexbox overflow */
    }

    .profile-section {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: 1px solid #e9ecef;
        width: 100%;
        box-sizing: border-box;
    }

    .profile-section h2 {
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
        color: #212529;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #007bff;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        width: 100%;
    }

    .info-item {
        padding: 1rem;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        background: #f8f9fa;
        width: 100%;
        box-sizing: border-box;
    }

    .info-item label {
        display: block;
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    .info-value {
        color: #212529;
        font-weight: 500;
    }

    .role-tag {
        display: inline-block;
        background: #007bff;
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.8rem;
        margin-right: 0.5rem;
        margin-bottom: 0.25rem;
    }

    .security-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        width: 100%;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        border: 1px solid #007bff;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s ease;
        cursor: pointer;
        font-size: 0.9rem;
        width: 100%;
        justify-content: center;
        box-sizing: border-box;
    }

    .btn-outline {
        background: white;
        color: #007bff;
    }

    .btn-outline:hover {
        background: #007bff;
        color: white;
        transform: translateY(-1px);
    }

    .btn-icon {
        font-size: 1rem;
    }

    .activity-list {
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
        word-wrap: break-word;
    }

    .activity-time {
        font-size: 0.8rem;
        color: #6c757d;
        word-wrap: break-word;
    }

    /* Responsive */
    @media (max-width: 1100px) {
        .profile-layout {
            max-width: 900px;
        }
    }

    @media (max-width: 968px) {
        .profile-layout {
            grid-template-columns: 1fr;
            max-width: 600px;
        }

        .profile-sidebar {
            width: 100%;
            max-width: 600px;
        }

        .profile-header h1 {
            font-size: 2rem;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }

        .security-actions {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .profile-layout {
            max-width: 100%;
            padding: 0 1rem;
        }

        .profile-section {
            padding: 1rem;
        }

        .profile-card {
            padding: 1rem;
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

    @media (max-width: 480px) {
        .profile-header h1 {
            font-size: 1.75rem;
        }

        .profile-section h2 {
            font-size: 1.25rem;
        }

        .btn {
            padding: 0.6rem 1rem;
            font-size: 0.85rem;
        }
    }
</style>
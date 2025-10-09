<?php
// App/Views/profile/index.php
?>

<div class="row">
    <div class="profile-header">
        <h1>Личный кабинет</h1>
        <p>Управление вашей учетной записью</p>
    </div>
</div>

<style>
    .profile-header {
        text-align: center;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e9ecef;
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
        grid-template-columns: 300px 1fr;
        gap: 2rem;
    }

    /* Sidebar */
    .profile-sidebar {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .profile-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: 1px solid #e9ecef;
        text-align: center;
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
    }

    .profile-email {
        color: #6c757d;
        margin: 0 0 1rem 0;
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
    }

    .profile-section {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: 1px solid #e9ecef;
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
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
    }

    .info-item {
        padding: 1rem;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        background: #f8f9fa;
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
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
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
    }

    .activity-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        background: #f8f9fa;
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
    }

    .activity-content {
        flex: 1;
    }

    .activity-content p {
        margin: 0 0 0.25rem 0;
        color: #212529;
    }

    .activity-time {
        font-size: 0.8rem;
        color: #6c757d;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .profile-layout {
            grid-template-columns: 1fr;
        }

        .profile-header h1 {
            font-size: 2rem;
        }

        .security-actions {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .info-grid {
            grid-template-columns: 1fr;
        }

        .profile-card {
            padding: 1rem;
        }
    }
</style>
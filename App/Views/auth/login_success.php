<?php
use App\Core\User;
?>

<div class="auth-page">
    <div class="auth-container compact">
        <div class="auth-header">
            <h2>Успешная авторизация</h2>
            <p>Приветствуем, <?= e(User::get('name')) ?>!</p>
        </div>

        <div class="auth-body">
            <p>Вы успешно авторизовались.</p>
            <div class="loading-spinner"></div>
            <p>Перенаправляем на главную...</p>
        </div>

        <div class="auth-footer">
            <p>Если вы не были перенаправлены, <a href="/">тык</a>.</p>
        </div>
    </div>
</div>

<script>
    // JavaScript редирект через 2 секунды
    setTimeout(function() {
        window.location.href = '/';
    }, 2000);
</script>

<style>
    .loading-spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3498db;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin: 20px auto;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
<?php
use App\Core\Helpers;
?>

<div class="hero">
    <h1>Добро пожаловать</h1>

    <?php if (!\App\Core\User::isLoggedIn()): ?>
        <p>Для того, чтобы начать работать в системе необходимо войти</p>
    <?php else: ?>
        <p>Добро пожаловать, <?= Helpers::e(\App\Core\User::getName()) ?>!</p>
        <p>Вы успешно авторизовались в системе.</p>

        <?php if (\App\Core\User::isAdmin()): ?>
            <div style="margin-top: 1rem; padding: 1rem; background: #e7f3ff; border-radius: 8px;">
                <p><strong>Административный доступ:</strong> У вас есть доступ к панели управления.</p>
                <a href="/admin" class="btn btn-primary" style="margin-top: 0.5rem;">Перейти в админ-панель</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div class="info-grid" style="margin-top: 2rem;">
    <div class="info-item">
        <div class="info-value">PHP <?= Helpers::e(phpversion()) ?></div>
        <div class="info-label">Версия PHP</div>
    </div>
    <div class="info-item">
        <div class="info-value"><?= Helpers::e(round(memory_get_usage(true) / 1024 / 1024, 1)) ?> MB</div>
        <div class="info-label">Использование памяти</div>
    </div>
    <div class="info-item">
        <div class="info-value"><?= Helpers::e(date('d.m.Y H:i')) ?></div>
        <div class="info-label">Текущее время</div>
    </div>
</div>
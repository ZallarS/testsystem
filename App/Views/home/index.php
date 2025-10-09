<div class="hero">
    <h1>Добро пожаловать</h1>

    <?php if (!\App\Core\User::isLoggedIn()): ?>
        <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem;">
            <a href="/register" class="btn btn-primary">Начать работу</a>
            <a href="/login" class="btn btn-outline">Войти в систему</a>
        </div>
    <?php else: ?>
        <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem;">
            <a href="/profile" class="btn btn-primary">Мой профиль</a>
            <?php if (\App\Core\User::isAdmin()): ?>
                <a href="/admin" class="btn btn-outline">Админ-панель</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php if ($isLoggedIn): ?>
    <div class="card">
        <h2>Ваш профиль</h2>
        <div class="profile-grid">
            <div class="profile-row">
                <span class="profile-label">Имя:</span>
                <strong class="profile-value"><?= htmlspecialchars($userName) ?></strong>
            </div>
            <div class="profile-row">
                <span class="profile-label">Email:</span>
                <strong class="profile-value"><?= htmlspecialchars($userEmail) ?></strong>
            </div>
            <div class="profile-row">
                <span class="profile-label">Роль:</span>
                <strong class="profile-value"><?= implode(', ', array_map('htmlspecialchars', $userRoles)) ?></strong>
            </div>
        </div>
    </div>
<?php endif; ?>
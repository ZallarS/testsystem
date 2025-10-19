<div class="hero">
    <h1>Добро пожаловать</h1>

    <?php if (!\App\Core\User::isLoggedIn()): ?>
        <p>Для того, чтобы начать работать в системе необходимо войти<p>
    <?php endif; ?>
</div>
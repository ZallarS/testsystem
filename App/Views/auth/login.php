

<div class="auth-container">
    <div class="card" style="max-width: 400px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <h1 style="font-size: 2rem; margin-bottom: 0.5rem;">Вход в систему</h1>
            <p style="color: #6c757d;">Введите ваши учетные данные</p>
        </div>

        <?php if (isset($error) && $error): ?>
            <div class="alert alert-error">
                <?= \App\Core\Helpers::e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/login" autocomplete="on">
            <?= \App\Core\Helpers::csrfField() ?>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       value="<?= isset($email) ? \App\Core\Helpers::e($email) : '' ?>"
                       required autocomplete="email"
                       placeholder="your@email.com"
                       pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$">
            </div>

            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password"
                       required autocomplete="current-password"
                       placeholder="Введите ваш пароль"
                       minlength="8">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                Войти в систему
            </button>
        </form>
    </div>
</div>
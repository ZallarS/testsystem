<?php
// Этот файл должен содержать ТОЛЬКО содержимое страницы входа
// без <html>, <head>, <body> тегов - они уже есть в layout/main.php
?>

<div class="auth-container">
    <div class="card" style="max-width: 400px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <h1 style="font-size: 2rem; margin-bottom: 0.5rem;">Вход в систему</h1>
            <p style="color: #6c757d;">Введите ваши учетные данные</p>
        </div>

        <?php if (isset($error) && $error): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $errorMsg): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($errorMsg) ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <form method="POST" action="/login">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::generateToken() ?>">

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required
                       placeholder="your@email.com">
            </div>

            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" required
                       placeholder="Введите ваш пароль">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                Войти в систему
            </button>
        </form>

        <div style="text-align: center; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e9ecef;">
            <p style="color: #6c757d; margin-bottom: 0.5rem;">Нет учетной записи?</p>
            <a href="/register" class="btn btn-outline" style="width: 100%;">
                Создать аккаунт
            </a>
        </div>
    </div>
</div>
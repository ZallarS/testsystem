<?php
// Этот файл должен содержать ТОЛЬКО содержимое страницы регистрации
// без <html>, <head>, <body> тегов - они уже есть в layout/main.php
?>

<div class="auth-container">
    <div class="card" style="max-width: 400px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <h1 style="font-size: 2rem; margin-bottom: 0.5rem;">Регистрация</h1>
            <p style="color: #6c757d;">Создайте новую учетную запись</p>
        </div>

        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $error): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <form method="POST" action="/register">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::generateToken() ?>">

            <div class="form-group">
                <label for="name">Имя</label>
                <input type="text" id="name" name="name" value="<?= isset($name) ? htmlspecialchars($name) : '' ?>" required
                       placeholder="Ваше полное имя">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required
                       placeholder="your@email.com">
            </div>

            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" required
                       placeholder="Минимум 8 символов">
                <div style="font-size: 0.8rem; color: #6c757d; margin-top: 0.25rem;">
                    Пароль должен содержать цифры, буквы и специальные символы
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Подтверждение пароля</label>
                <input type="password" id="confirm_password" name="confirm_password" required
                       placeholder="Повторите пароль">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                Создать аккаунт
            </button>
        </form>

        <div style="text-align: center; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e9ecef;">
            <p style="color: #6c757d; margin-bottom: 0.5rem;">Уже есть аккаунт?</p>
            <a href="/login" class="btn btn-outline" style="width: 100%;">
                Войти в систему
            </a>
        </div>
    </div>
</div>
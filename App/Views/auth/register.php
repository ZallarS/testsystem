<?php
use App\Core\User;
?>

<div class="auth-page">
    <div class="auth-container compact">
        <div class="auth-header">
            <h2>Регистрация</h2>
            <p>Создайте аккаунт, чтобы начать пользоваться системой</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <p>Пожалуйста, исправьте следующие ошибки:</p>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="/register" method="POST" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::generateToken() ?>">
            <div class="form-group">
                <label for="name">Псевдоним</label>
                <input type="text" id="name" name="name" value="<?= isset($name) ? htmlspecialchars($name) : '' ?>" required placeholder="Username123">
                <?php if (isset($errors['name'])): ?>
                    <div class="error-text"><?= htmlspecialchars($errors['name']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email">Электронная почта</label>
                <input type="email" id="email" name="email" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required placeholder="test@test.ru">
                <?php if (isset($errors['email'])): ?>
                    <div class="error-text"><?= htmlspecialchars($errors['email']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" required placeholder="********">
                <?php if (isset($errors['password'])): ?>
                    <div class="error-text"><?= htmlspecialchars($errors['password']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="confirm_password">Повторить пароль</label>
                <input type="password" id="confirm_password" name="confirm_password" required placeholder="********">
                <?php if (isset($errors['confirm_password'])): ?>
                    <div class="error-text"><?= htmlspecialchars($errors['confirm_password']) ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Создать аккаунт</button>
        </form>

        <div class="auth-footer">
            <p>Уже есть аккаунт? <a href="/login">Войти</a></p>
            <p><a href="/">← На главную</a></p>
        </div>
    </div>
</div>

<style>
    .auth-page {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 70vh;
        padding: 2rem 1rem;
    }

    .auth-container.compact {
        width: 100%;
        max-width: 400px;
        background: white;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .auth-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .auth-header h2 {
        font-size: 1.75rem;
        color: #2c3e50;
        margin-bottom: 0.5rem;
    }

    .auth-header p {
        color: #7f8c8d;
        font-size: 1rem;
    }

    .auth-form {
        margin-bottom: 1.5rem;
    }

    .form-group {
        margin-bottom: 1.25rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #2c3e50;
        font-size: 0.9rem;
    }

    .form-group input {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 1rem;
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    .form-group input:focus {
        border-color: #3498db;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        outline: none;
    }

    .error-text {
        color: #e74c3c;
        font-size: 0.85rem;
        margin-top: 0.5rem;
    }

    .btn-full {
        width: 100%;
        padding: 0.875rem;
        font-size: 1rem;
        font-weight: 600;
    }

    .auth-footer {
        text-align: center;
        border-top: 1px solid #eee;
        padding-top: 1.5rem;
    }

    .auth-footer p {
        margin: 0.75rem 0;
        font-size: 0.9rem;
        color: #7f8c8d;
    }

    .auth-footer a {
        color: #3498db;
        text-decoration: none;
        transition: color 0.3s;
    }

    .auth-footer a:hover {
        color: #2980b9;
        text-decoration: underline;
    }

    /* Адаптивность для мобильных устройств */
    @media (max-width: 480px) {
        .auth-container.compact {
            padding: 1.5rem;
            margin: 0 0.5rem;
        }

        .auth-header h2 {
            font-size: 1.5rem;
        }
    }
</style>
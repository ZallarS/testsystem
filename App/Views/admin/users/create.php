<?php
// views/admin/users/create.php
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Добавление пользователя</h1>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="/admin/users/store">
    <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::generateToken() ?>">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Основная информация</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Имя пользователя *</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= isset($name) ? htmlspecialchars($name) : '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email адрес *</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="roles" class="form-label">Роли пользователя *</label>
                        <select class="form-select" id="roles" name="roles[]" multiple required>
                            <?php foreach ($availableRoles as $roleOption): ?>
                                <option value="<?= $roleOption ?>" <?= (isset($roles) && in_array($roleOption, $roles)) ? 'selected' : '' ?>>
                                    <?= ucfirst($roleOption) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Для выбора нескольких ролей удерживайте Ctrl (Cmd на Mac)</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Безопасность</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="password" class="form-label">Пароль *</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="form-text">Пароль должен содержать не менее 6 символов</div>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Подтверждение пароля *</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="forcePasswordChange" name="forcePasswordChange">
                        <label class="form-check-label" for="forcePasswordChange">Требовать смены пароля при следующем входе</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary">Создать пользователя</button>
        <a href="/admin/users" class="btn btn-secondary">Отмена</a>
    </div>
</form>
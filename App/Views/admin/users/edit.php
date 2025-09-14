<?php

?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Редактирование пользователя: <?= htmlspecialchars($user['name']) ?></h1>
</div>

<form method="POST" action="/admin/users/update/<?= $user['id'] ?>">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Основная информация</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Имя пользователя</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email адрес</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Роль пользователя</label>
                        <select class="form-select" id="role" name="role" required>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role ?>" <?= $user['role'] === $role ? 'selected' : '' ?>>
                                    <?= ucfirst($role) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
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
                        <label for="password" class="form-label">Новый пароль</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Оставьте пустым, чтобы не изменять">
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Подтверждение пароля</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="forcePasswordChange" name="forcePasswordChange">
                        <label class="form-check-label" for="forcePasswordChange">Требовать смены пароля при следующем входе</label>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title">Дополнительная информация</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>ID пользователя:</strong> <?= $user['id'] ?>
                    </div>
                    <div class="mb-2">
                        <strong>Дата регистрации:</strong> <?= date('d.m.Y H:i', strtotime($user['created_at'])) ?>
                    </div>
                    <div class="mb-2">
                        <strong>Последнее обновление:</strong> <?= date('d.m.Y H:i', strtotime($user['updated_at'] ?? $user['created_at'])) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary">Сохранить изменения</button>
        <a href="/admin/users" class="btn btn-secondary">Отмена</a>
    </div>
</form>
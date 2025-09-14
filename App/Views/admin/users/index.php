<?php
// views/admin/users/index.php
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Управление пользователями</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/users/create" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-circle"></i> Добавить пользователя
        </a>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th>ID</th>
            <th>Имя</th>
            <th>Email</th>
            <th>Роль</th>
            <th>Дата регистрации</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>

                <td>
                    <?php
                    $userRoles = explode(',', $user['roles'] ?? '');
                    foreach ($userRoles as $role):
                        $badgeClass =
                            $role === 'admin' ? 'danger' :
                                ($role === 'moderator' ? 'warning' : 'secondary');
                        ?>
                        <span class="badge bg-<?= $badgeClass ?> me-1">
                            <?= ucfirst(trim($role)) ?>
                        </span>
                        <?php endforeach; ?>
                </td>

                <td><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <a href="/admin/users/edit/<?= $user['id'] ?>" class="btn btn-outline-primary">
                            <i class="bi bi-pencil"></i> Редактировать
                        </a>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteUserModal<?= $user['id'] ?>">
                            <i class="bi bi-trash"></i> Удалить
                        </button>
                    </div>

                    <!-- Модальное окно подтверждения удаления -->
                    <div class="modal fade" id="deleteUserModal<?= $user['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Подтверждение удаления</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Вы уверены, что хотите удалить пользователя <strong><?= htmlspecialchars($user['name']) ?></strong>?</p>
                                    <p class="text-danger">Это действие нельзя отменить.</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                    <form action="/admin/users/delete/<?= $user['id'] ?>" method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::generateToken() ?>">
                                        <button type="submit" class="btn btn-danger">Удалить</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<nav aria-label="Page navigation">
    <ul class="pagination justify-content-center">
        <li class="page-item disabled">
            <a class="page-link" href="#">Предыдущая</a>
        </li>
        <li class="page-item active"><a class="page-link" href="#">1</a></li>
        <li class="page-item"><a class="page-link" href="#">2</a></li>
        <li class="page-item"><a class="page-link" href="#">3</a></li>
        <li class="page-item">
            <a class="page-link" href="#">Следующая</a>
        </li>
    </ul>
</nav>
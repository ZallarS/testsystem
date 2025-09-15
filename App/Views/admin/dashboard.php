<!-- Статистика -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card text-center">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-users text-primary"></i></h5>
                <h3 class="card-text"><?= $stats['totalUsers'] ?></h3>
                <p class="text-muted">Всего пользователей</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card text-center">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-user-check text-success"></i></h5>
                <h3 class="card-text"><?= $stats['activeUsers'] ?></h3>
                <p class="text-muted">Активных пользователей</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card text-center">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-plug text-warning"></i></h5>
                <h3 class="card-text"><?= $stats['totalPlugins'] ?></h3>
                <p class="text-muted">Всего плагинов</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card text-center">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-power-off text-danger"></i></h5>
                <h3 class="card-text"><?= $stats['activePlugins'] ?></h3>
                <p class="text-muted">Активных плагинов</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Последние пользователи -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-users me-2"></i>Последние пользователи
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Имя</th>
                            <th>Email</th>
                            <th>Роль</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($recentUsers as $user): ?>
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
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <a href="/admin/users" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-eye me-1"></i>Все пользователи
                </a>
            </div>
        </div>
    </div>

    <!-- Быстрые действия -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-bolt me-2"></i>Быстрые действия
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="/admin/users" class="btn btn-outline-primary">
                        <i class="fas fa-users me-2"></i> Управление пользователями
                    </a>
                    <a href="/admin/plugins" class="btn btn-outline-success">
                        <i class="fas fa-plug me-2"></i> Управление плагинами
                    </a>
                    <a href="/admin/settings" class="btn btn-outline-secondary">
                        <i class="fas fa-cog me-2"></i> Настройки системы
                    </a>
                </div>
            </div>
        </div>

        <!-- Статус системы -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-server me-2"></i>Статус системы
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <span class="badge bg-success">Online</span>
                    <span class="ms-2">Система работает стабильно</span>
                </div>
                <div class="mb-2">
                    <span class="badge bg-info">PHP <?= phpversion() ?></span>
                    <span class="ms-2">Версия PHP</span>
                </div>
                <div>
                    <span class="badge bg-secondary"><?= number_format(memory_get_usage() / 1024 / 1024, 2) ?> MB</span>
                    <span class="ms-2">Использование памяти</span>
                </div>
            </div>
        </div>
    </div>
</div>
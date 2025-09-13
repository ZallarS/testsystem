<div class="container">
    <h1>User Management</h1>

    <?php if (!empty($_GET['message'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_GET['message']) ?>
        </div>
    <?php endif; ?>

    <table class="table table-striped">
        <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= e($user['id']) ?></td>
                <td><?= e($user['name']) ?></td>
                <td><?= e($user['email']) ?></td>
                <td>
                    <span class="badge bg-<?=
                    e($user['role']) === 'admin' ? 'danger' :
                        (e($user['role']) === 'moderator' ? 'warning' : 'secondary')
                    ?>">
                        <?= ucfirst(e($user['role'])) ?>
                    </span>
                </td>
                <td>
                    <a href="/admin/users/edit/<?= e($user['id']) ?>" class="btn btn-sm btn-primary">Edit</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
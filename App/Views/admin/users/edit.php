<div class="container">
    <h1>Edit User: <?= htmlspecialchars($user['name']) ?></h1>

    <?php if (!empty($_GET['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/admin/users/update/<?= $user['id'] ?>">
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Role</label>
            <select class="form-select" name="role">
                <?php foreach ($roles as $role): ?>
                    <option value="<?= $role ?>" <?= $user['role'] === $role ? 'selected' : '' ?>>
                        <?= ucfirst($role) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Update User</button>
        <a href="/admin/users" class="btn btn-secondary">Cancel</a>
    </form>
</div>
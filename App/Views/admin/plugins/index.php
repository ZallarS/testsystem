<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root {
        --primary: #4361ee;
        --success: #06d6a0;
        --secondary: #64748b;
        --light-bg: #f8fafc;
        --card-border: #e2e8f0;
    }

    body {
        background-color: #f8fafc;
        color: #334155;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .container {
        max-width: 1000px;
    }

    .header-title {
        font-weight: 600;
        color: #1e293b;
    }

    .filter-btn {
        border: 1px solid #e2e8f0;
        background: white;
        color: #64748b;
        font-weight: 500;
        padding: 0.35rem 0.75rem;
        border-radius: 6px;
        transition: all 0.2s ease;
    }

    .filter-btn.active, .filter-btn:hover {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .stat-card {
        background: white;
        border: 1px solid var(--card-border);
        border-radius: 12px;
        transition: transform 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
    }

    .stat-title {
        font-size: 0.875rem;
        color: #64748b;
        font-weight: 500;
    }

    .stat-value {
        font-weight: 600;
        color: #1e293b;
        font-size: 1.5rem;
    }

    .search-container {
        position: relative;
    }

    .search-input {
        padding-left: 2.5rem;
        border: 1px solid var(--card-border);
        border-radius: 8px;
        background: white;
    }

    .search-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    .plugin-card {
        background: white;
        border: 1px solid var(--card-border);
        border-radius: 12px;
        transition: all 0.2s ease;
        margin-bottom: 1rem;
    }

    .plugin-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }

    .plugin-name {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.25rem;
    }

    .plugin-version {
        color: #64748b;
        font-size: 0.875rem;
    }

    .plugin-description {
        color: #475569;
        line-height: 1.5;
    }

    .plugin-meta {
        color: #64748b;
        font-size: 0.875rem;
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 100px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .status-active {
        background: rgba(6, 214, 160, 0.1);
        color: var(--success);
    }

    .status-inactive {
        background: rgba(100, 116, 139, 0.1);
        color: var(--secondary);
    }

    .btn-action {
        padding: 0.4rem 1rem;
        border-radius: 8px;
        font-weight: 500;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }

    .btn-activate {
        background: var(--primary);
        color: white;
        border: 1px solid var(--primary);
    }

    .btn-activate:hover {
        background: #2f46d4;
        border-color: #2f46d4;
        color: white;
    }

    .btn-deactivate {
        background: white;
        color: #ef4444;
        border: 1px solid #e2e8f0;
    }

    .btn-deactivate:hover {
        background: #fef2f2;
        border-color: #fecaca;
    }

    .btn-settings {
        background: white;
        color: var(--primary);
        border: 1px solid #e2e8f0;
    }

    .btn-settings:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    .dependencies {
        font-size: 0.875rem;
        color: #64748b;
        border-top: 1px solid #f1f5f9;
        padding-top: 0.75rem;
        margin-top: 1rem;
    }

    .empty-state {
        padding: 3rem 1rem;
        text-align: center;
        color: #94a3b8;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
    }

    .alert-message {
        border-radius: 8px;
        border: none;
        padding: 0.875rem 1rem;
    }
</style>

<div class="container py-4">
    <!-- Заголовок и фильтры -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="header-title mb-0">Управление плагинами</h1>
        <div class="d-flex gap-2">
            <button type="button" class="filter-btn active" data-filter="all">Все</button>
            <button type="button" class="filter-btn" data-filter="active">Активные</button>
            <button type="button" class="filter-btn" data-filter="inactive">Неактивные</button>
        </div>
    </div>

    <!-- Сообщения -->
    <?php if (!empty($_GET['message'])): ?>
        <div class="alert-message alert alert-<?= strpos($_GET['message'], 'Error') !== false ? 'danger' : 'success' ?> alert-dismissible fade show mb-4">
            <?= htmlspecialchars($_GET['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Статистика -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="stat-card h-100 p-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-title">Всего плагинов</div>
                        <div class="stat-value"><?= count($plugins) ?></div>
                    </div>
                    <div class="flex-shrink-0 text-primary">
                        <i class="fas fa-cube"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="stat-card h-100 p-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-title">Активных</div>
                        <div class="stat-value text-success"><?= count($activePlugins) ?></div>
                    </div>
                    <div class="flex-shrink-0 text-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card h-100 p-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="stat-title">Неактивных</div>
                        <div class="stat-value text-secondary"><?= count($plugins) - count($activePlugins) ?></div>
                    </div>
                    <div class="flex-shrink-0 text-secondary">
                        <i class="fas fa-ban"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Поиск -->
    <div class="search-container mb-4">
        <i class="search-icon fas fa-search"></i>
        <input type="text" class="form-control search-input py-2" placeholder="Поиск плагинов..." id="pluginSearch">
    </div>

    <!-- Список плагинов -->
    <div class="plugins-list">
        <?php
        // Сортируем плагины: сначала активные, затем неактивные
        $sortedPlugins = [];
        foreach ($plugins as $pluginName => $plugin) {
            $sortedPlugins[$pluginName] = [
                'plugin' => $plugin,
                'active' => isset($activePlugins[$pluginName])
            ];
        }

        uasort($sortedPlugins, function($a, $b) {
            if ($a['active'] && !$b['active']) return -1;
            if (!$a['active'] && $b['active']) return 1;
            return strcmp($a['plugin']->getName(), $b['plugin']->getName());
        });
        ?>

        <?php foreach ($sortedPlugins as $pluginName => $pluginData):
            $plugin = $pluginData['plugin'];
            $isActive = $pluginData['active'];
            ?>
            <div class="plugin-card p-4" data-status="<?= $isActive ? 'active' : 'inactive' ?>">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <div class="d-flex align-items-center mb-1">
                            <h3 class="plugin-name mb-0 me-2"><?= htmlspecialchars($plugin->getName()) ?></h3>
                            <span class="plugin-version">v<?= htmlspecialchars($plugin->getVersion()) ?></span>
                        </div>
                        <span class="status-badge status-<?= $isActive ? 'active' : 'inactive' ?>">
                            <?= $isActive ? 'Активен' : 'Неактивен' ?>
                        </span>
                    </div>

                    <div class="d-flex gap-2">
                        <?php if ($isActive): ?>
                            <a href="/admin/plugins/deactivate/<?= urlencode($pluginName) ?>?v=<?= time() ?>"
                               class="btn btn-deactivate"
                               onclick="return confirm('Вы уверены, что хотите деактивировать этот плагин?')">
                                Деактивировать
                            </a>
                            <?php if (method_exists($plugin, 'getSettingsUrl') && $plugin->getSettingsUrl()): ?>
                                <a href="<?= $plugin->getSettingsUrl() ?>" class="btn btn-settings">
                                    <i class="fas fa-cog me-1"></i>
                                    Настройки
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="/admin/plugins/activate/<?= urlencode($pluginName) ?>?v=<?= time() ?>"
                               class="btn btn-activate"
                               onclick="return confirm('Вы уверены, что хотите активировать этот плагин?')">
                                Активировать
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <p class="plugin-description mb-3"><?= htmlspecialchars($plugin->getDescription()) ?></p>

                <div class="plugin-meta">
                    <span class="me-3">
                        <i class="fas fa-user me-1"></i>
                        <?= htmlspecialchars($plugin->getAuthor()) ?>
                    </span>
                    <?php if (method_exists($plugin, 'getWebsite') && $plugin->getWebsite()): ?>
                        <a href="<?= htmlspecialchars($plugin->getWebsite()) ?>" target="_blank" class="text-secondary text-decoration-none">
                            <i class="fas fa-globe me-1"></i>
                            Сайт
                        </a>
                    <?php endif; ?>
                </div>

                <?php if (method_exists($plugin, 'getDependencies') && !empty($plugin->getDependencies())): ?>
                    <div class="dependencies">
                        <span class="fw-medium">Зависимости:</span>
                        <?= implode(', ', array_map('htmlspecialchars', $plugin->getDependencies())) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Если плагинов нет -->
    <?php if (empty($plugins)): ?>
        <div class="empty-state">
            <i class="fas fa-cube"></i>
            <h4 class="mb-2">Плагины не найдены</h4>
            <p class="mb-0">В системе не обнаружено установленных плагинов.</p>
        </div>
    <?php endif; ?>
</div>

<!-- JavaScript для фильтрации и поиска -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Фильтрация по статусу
        const filterButtons = document.querySelectorAll('[data-filter]');
        const pluginItems = document.querySelectorAll('.plugin-card');
        const searchInput = document.getElementById('pluginSearch');

        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                const filter = this.getAttribute('data-filter');

                // Активируем кнопку
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                // Фильтруем плагины
                pluginItems.forEach(item => {
                    const status = item.getAttribute('data-status');
                    const isVisible = filter === 'all' || filter === status;

                    item.style.display = isVisible ? 'block' : 'none';
                });
            });
        });

        // Поиск плагинов
        searchInput.addEventListener('input', function() {
            const searchText = this.value.toLowerCase();

            pluginItems.forEach(item => {
                const pluginName = item.querySelector('.plugin-name').textContent.toLowerCase();
                const pluginDesc = item.querySelector('.plugin-description').textContent.toLowerCase();

                if (pluginName.includes(searchText) || pluginDesc.includes(searchText)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
</script>

<style>
    .plugin-item {
        transition: all 0.2s ease;
        border-radius: 8px;
    }

    .plugin-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05) !important;
    }

    .card {
        border-radius: 8px;
    }

    .btn {
        border-radius: 6px;
        font-weight: 500;
    }

    .alert {
        border-radius: 8px;
        border: none;
    }

    .badge {
        font-size: 0.7rem;
        padding: 0.35em 0.65em;
    }

    .input-group-text {
        border-radius: 8px 0 0 8px;
    }

    .form-control {
        border-radius: 0 8px 8px 0;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></s
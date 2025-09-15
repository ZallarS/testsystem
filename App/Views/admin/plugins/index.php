<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-puzzle-piece me-2"></i>Расширенная система плагинов
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="checkUpdatesBtn">
                <i class="fas fa-sync-alt me-1"></i> Проверить обновления
            </button>
            <button type="button" class="btn btn-sm btn-primary" id="uploadPluginBtn">
                <i class="fas fa-upload me-1"></i> Загрузить плагин
            </button>
            <button type="button" class="btn btn-sm btn-info" id="pluginMarketplaceBtn">
                <i class="fas fa-store me-1"></i> Магазин плагинов
            </button>
        </div>
    </div>
</div>

<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>
    Система плагинов теперь поддерживает расширенные возможности: переопределение маршрутов, модификацию представлений и добавление консольных команд.
</div>

<!-- Статистика -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card text-center">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-cube text-primary"></i></h5>
                <h3 class="card-text"><?= count($plugins) ?></h3>
                <p class="text-muted">Всего плагинов</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card text-center">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-plug text-success"></i></h5>
                <h3 class="card-text" style="color: var(--bs-success);"><?= count($activePlugins) ?></h3>
                <p class="text-muted">Активные</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card text-center">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-ban text-secondary"></i></h5>
                <h3 class="card-text" style="color: var(--bs-secondary);"><?= count($plugins) - count($activePlugins) ?></h3>
                <p class="text-muted">Неактивные</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card text-center">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-arrow-circle-up text-warning"></i></h5>
                <h3 class="card-text" style="color: var(--bs-warning);">2</h3>
                <p class="text-muted">Доступно обновлений</p>
            </div>
        </div>
    </div>
</div>

<!-- Элементы управления -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="btn-group me-3 mb-2">
                <button type="button" class="btn btn-sm btn-outline-secondary active" data-filter="all">Все</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="active">Активные</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="inactive">Неактивные</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="updates">С обновлениями</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="extensions">Расширения</button>
            </div>

            <div class="d-flex mb-2">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" placeholder="Поиск плагинов..." id="pluginSearch">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Список плагинов -->
<div class="row" id="pluginGrid">
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
        $hasUpdate = in_array($pluginName, ['TestPlugin', 'AnalyticsPlugin']);
        $isExtension = in_array($pluginName, ['UIExtension', 'PopupManager']);

        // Определяем возможности плагина
        $capabilities = [];
        if (method_exists($plugin, 'getRoutes') && !empty($plugin->getRoutes())) {
            $capabilities[] = 'Маршруты';
        }
        if (method_exists($plugin, 'getViews') && !empty($plugin->getViews())) {
            $capabilities[] = 'Представления';
        }
        if (method_exists($plugin, 'getConsoleCommands') && !empty($plugin->getConsoleCommands())) {
            $capabilities[] = 'Консоль';
        }
        if (method_exists($plugin, 'getHooks') && !empty($plugin->getHooks())) {
            $capabilities[] = 'Хуки';
        }

        // Определяем зависимости
        $dependencies = [];
        if (method_exists($plugin, 'getDependencies')) {
            $dependencies = $plugin->getDependencies();
        }
        ?>
        <div class="col-md-6 col-lg-4 mb-4" data-status="<?= $isActive ? 'active' : 'inactive' ?>" data-update="<?= $hasUpdate ? 'true' : 'false' ?>" data-extension="<?= $isExtension ? 'true' : 'false' ?>">
            <div class="card plugin-card h-100">
                <div class="card-header d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-1">
                            <?= htmlspecialchars($plugin->getName()) ?>
                            <?php if ($pluginName === 'TestPlugin'): ?>
                                <i class="fas fa-star text-warning" title="Рекомендуемый плагин"></i>
                            <?php endif; ?>
                            <?php if ($isExtension): ?>
                                <i class="fas fa-puzzle-piece text-info" title="Расширение для других плагинов"></i>
                            <?php endif; ?>
                        </h5>
                        <div class="text-muted small">v<?= htmlspecialchars($plugin->getVersion()) ?></div>
                    </div>
                    <span class="badge bg-<?= $isActive ? 'success' : 'secondary' ?>">
                        <?= $isActive ? 'Активен' : 'Неактивен' ?>
                    </span>
                </div>
                <div class="card-body">
                    <p class="card-text"><?= htmlspecialchars($plugin->getDescription()) ?></p>

                    <?php if ($hasUpdate): ?>
                        <div class="alert alert-warning py-2 mb-3">
                            <i class="fas fa-arrow-circle-up me-1"></i>
                            Доступно обновление до версии 2.1.0
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <h6 class="card-subtitle mb-1">Возможности:</h6>
                        <div>
                            <?php foreach ($capabilities as $capability): ?>
                                <span class="badge bg-primary me-1 capability-tag"><?= $capability ?></span>
                            <?php endforeach; ?>
                            <?php if (empty($capabilities)): ?>
                                <span class="badge bg-secondary me-1 capability-tag">Базовые</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($dependencies)): ?>
                        <div class="mb-3">
                            <h6 class="card-subtitle mb-1">Зависимости:</h6>
                            <div>
                                <?php foreach ($dependencies as $dep => $version):
                                    $depInstalled = isset($plugins[$dep]);
                                    $depActive = isset($activePlugins[$dep]);
                                    $statusClass = $depInstalled ? ($depActive ? 'status-installed' : 'status-inactive') : 'status-missing';
                                    ?>
                                    <div class="mb-1">
                                        <span class="dependency-status <?= $statusClass ?>"></span>
                                        <span class="small"><?= $dep ?> (<?= $version ?>)</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="bg-light p-2 rounded mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Совместимость:</span>
                            <span class="fw-medium">v1.2+</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Последнее обновление:</span>
                            <span class="fw-medium">2 недели назад</span>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span>Рейтинг:</span>
                            <span class="fw-medium">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star-half-alt text-warning"></i>
                                4.5
                            </span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">
                            <i class="fas fa-user me-1"></i>
                            <?= htmlspecialchars($plugin->getAuthor()) ?>
                        </span>
                        <?php if (method_exists($plugin, 'getWebsite') && $plugin->getWebsite()): ?>
                            <a href="<?= htmlspecialchars($plugin->getWebsite()) ?>" target="_blank" class="text-decoration-none">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <?php if ($isActive): ?>
                            <a href="/admin/plugins/deactivate/<?= urlencode($pluginName) ?>?v=<?= time() ?>"
                               class="btn btn-sm btn-danger mb-1"
                               onclick="return confirm('Вы уверены, что хотите деактивировать этот плагин?')">
                                <i class="fas fa-power-off me-1"></i> Деактивировать
                            </a>
                            <?php if (method_exists($plugin, 'getSettingsUrl') && $plugin->getSettingsUrl()): ?>
                                <a href="<?= $plugin->getSettingsUrl() ?>" class="btn btn-sm btn-outline-secondary mb-1">
                                    <i class="fas fa-cog me-1"></i> Настройки
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="/admin/plugins/activate/<?= urlencode($pluginName) ?>?v=<?= time() ?>"
                               class="btn btn-sm btn-primary mb-1"
                               onclick="return confirm('Вы уверены, что хотите активировать этот плагин?')">
                                <i class="fas fa-plug me-1"></i> Активировать
                            </a>
                        <?php endif; ?>

                        <?php if ($hasUpdate): ?>
                            <button class="btn btn-sm btn-success mb-1 update-plugin-btn" data-plugin="<?= htmlspecialchars($pluginName) ?>">
                                <i class="fas fa-download me-1"></i> Обновить
                            </button>
                        <?php endif; ?>

                        <button class="btn btn-sm btn-outline-info mb-1 plugin-info-btn" data-plugin="<?= htmlspecialchars($pluginName) ?>">
                            <i class="fas fa-info-circle me-1"></i> Подробнее
                        </button>

                        <button class="btn btn-sm btn-outline-secondary mb-1 expand-btn" title="Подробнее">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                </div>

                <div class="plugin-expanded-content p-3 border-top">
                    <h6>Расширенная информация</h6>
                    <p class="small">Этот плагин расширяет функциональность системы следующими способами:</p>
                    <ul class="small">
                        <li>Добавляет новые маршруты для API</li>
                        <li>Переопределяет стандартные представления</li>
                        <li>Добавляет консольные команды для управления</li>
                        <li>Регистрирует хуки для расширения функциональности</li>
                    </ul>

                    <h6>Дополнительные требования</h6>
                    <p class="small">Для работы этого плагина требуется PHP 7.4+ и следующие расширения:</p>
                    <ul class="small">
                        <li>JSON</li>
                        <li>MBString</li>
                        <li>CURL</li>
                    </ul>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Если плагинов нет -->
    <?php if (empty($plugins)): ?>
        <div class="col-12 text-center py-5">
            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
            <h3 class="text-muted">Плагины не найдены</h3>
            <p class="text-muted">В системе не обнаружено установленных плагинов.</p>
            <button type="button" class="btn btn-primary mt-2" id="uploadPluginEmptyBtn">
                <i class="fas fa-upload me-1"></i> Загрузить первый плагин
            </button>
        </div>
    <?php endif; ?>
</div>
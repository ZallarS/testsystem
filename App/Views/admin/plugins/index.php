<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление плагинами | Система тестирований</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #eef2ff;
            --success: #10b981;
            --success-light: #ecfdf5;
            --warning: #f59e0b;
            --warning-light: #fffbeb;
            --danger: #ef476f;
            --danger-light: #fef2f2;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --radius: 8px;
            --radius-lg: 12px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--gray-50);
            color: var(--gray-900);
            line-height: 1.5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .header-title {
            font-weight: 700;
            font-size: 1.875rem;
            color: var(--gray-900);
        }

        .header-actions {
            display: flex;
            gap: 0.75rem;
        }

        .btn {
            padding: 0.625rem 1.25rem;
            border-radius: var(--radius);
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid transparent;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: #3730a3;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: white;
            color: var(--gray-700);
            border-color: var(--gray-300);
        }

        .btn-secondary:hover {
            background: var(--gray-50);
            transform: translateY(-1px);
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-success:hover {
            background: #059669;
            transform: translateY(-1px);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            transition: all 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .stat-title {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stat-value {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--gray-900);
        }

        .controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
            background: white;
            padding: 1.25rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
        }

        .filter-tabs {
            display: flex;
            background: var(--gray-100);
            border-radius: var(--radius);
            padding: 0.25rem;
        }

        .filter-tab {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            background: transparent;
            color: var(--gray-600);
        }

        .filter-tab.active {
            background: white;
            color: var(--gray-900);
            box-shadow: var(--shadow-sm);
        }

        .search-box {
            position: relative;
            min-width: 280px;
        }

        .search-input {
            width: 100%;
            padding: 0.625rem 1rem 0.625rem 2.75rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            font-size: 0.875rem;
            background: white;
            transition: border-color 0.2s;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-600);
        }

        .plugin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        .plugin-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            overflow: hidden;
            transition: all 0.2s;
            display: flex;
            flex-direction: column;
        }

        .plugin-card:hover {
            box-shadow: var(--shadow);
            transform: translateY(-2px);
        }

        .plugin-header {
            padding: 1.25rem 1.5rem 0;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.75rem;
        }

        .plugin-title-container {
            flex: 1;
        }

        .plugin-title {
            font-weight: 600;
            font-size: 1.125rem;
            color: var(--gray-900);
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .plugin-version {
            font-size: 0.875rem;
            color: var(--gray-600);
        }

        .plugin-status {
            padding: 0.25rem 0.75rem;
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-active {
            background: var(--success-light);
            color: var(--success);
        }

        .status-inactive {
            background: var(--gray-100);
            color: var(--gray-600);
        }

        .plugin-body {
            padding: 0 1.5rem 1.25rem;
            flex: 1;
        }

        .plugin-description {
            color: var(--gray-700);
            margin-bottom: 1.25rem;
            line-height: 1.6;
        }

        .plugin-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-bottom: 1rem;
        }

        .plugin-author {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .plugin-details {
            background: var(--gray-50);
            padding: 0.75rem;
            border-radius: var(--radius);
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .detail-item:last-child {
            margin-bottom: 0;
        }

        .detail-label {
            color: var(--gray-600);
        }

        .detail-value {
            font-weight: 500;
            color: var(--gray-800);
        }

        .update-available {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem;
            background: var(--warning-light);
            color: var(--warning);
            border-radius: var(--radius);
            margin-bottom: 1rem;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .plugin-actions {
            padding: 1rem 1.5rem;
            background: var(--gray-50);
            border-top: 1px solid var(--gray-200);
            display: flex;
            gap: 0.75rem;
        }

        .btn-sm {
            padding: 0.5rem 0.75rem;
            font-size: 0.8125rem;
        }

        .btn-danger {
            background: white;
            color: #dc2626;
            border-color: var(--gray-300);
        }

        .btn-danger:hover {
            background: #fef2f2;
            transform: translateY(-1px);
        }

        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 3rem 1rem;
            color: var(--gray-600);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--gray-300);
        }

        .alert {
            padding: 1rem 1.25rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success {
            background: var(--success-light);
            color: var(--success);
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        /* Модальное окно */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 1rem;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            width: 100%;
            max-width: 500px;
            overflow: hidden;
            transform: translateY(20px);
            transition: transform 0.3s;
        }

        .modal-overlay.active .modal {
            transform: translateY(0);
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-weight: 600;
            font-size: 1.25rem;
            color: var(--gray-900);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--gray-500);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-label {
            font-weight: 500;
            font-size: 0.875rem;
            color: var(--gray-700);
        }

        .form-input {
            padding: 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            font-size: 0.875rem;
            transition: border-color 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }

        .form-file {
            padding: 0.75rem;
            border: 2px dashed var(--gray-300);
            border-radius: var(--radius);
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .form-file:hover {
            border-color: var(--primary);
            background: var(--primary-light);
        }

        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }

        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--gray-200);
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        @media (max-width: 768px) {
            .plugin-grid {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .controls {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-tabs {
                width: 100%;
                justify-content: center;
            }

            .search-box {
                width: 100%;
            }

            .plugin-actions {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1 class="header-title">Управление плагинами</h1>
        <div class="header-actions">
            <button class="btn btn-secondary" id="checkUpdatesBtn">
                <i class="fas fa-sync-alt"></i> Проверить обновления
            </button>
            <button class="btn btn-primary" id="uploadPluginBtn">
                <i class="fas fa-upload"></i> Загрузить плагин
            </button>
        </div>
    </div>

    <!-- Уведомления -->
    <?php if (!empty($_GET['message'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($_GET['message']) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_GET['error'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <!-- Статистика -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-cube"></i> Всего плагинов</div>
            <div class="stat-value"><?= count($plugins) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-plug"></i> Активные</div>
            <div class="stat-value" style="color: var(--success);"><?= count($activePlugins) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-ban"></i> Неактивные</div>
            <div class="stat-value" style="color: var(--gray-600);"><?= count($plugins) - count($activePlugins) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-arrow-circle-up"></i> Доступно обновлений</div>
            <div class="stat-value" style="color: var(--warning);">2</div>
        </div>
    </div>

    <!-- Элементы управления -->
    <div class="controls">
        <div class="filter-tabs">
            <button class="filter-tab active" data-filter="all">Все</button>
            <button class="filter-tab" data-filter="active">Активные</button>
            <button class="filter-tab" data-filter="inactive">Неактивные</button>
            <button class="filter-tab" data-filter="updates">С обновлениями</button>
        </div>

        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" placeholder="Поиск плагинов..." id="pluginSearch">
        </div>
    </div>

    <!-- Список плагинов -->
    <div class="plugin-grid">
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
            $hasUpdate = in_array($pluginName, ['TestPlugin', 'AnalyticsPlugin']); // Пример для демонстрации
            ?>
            <div class="plugin-card" data-status="<?= $isActive ? 'active' : 'inactive' ?>" data-update="<?= $hasUpdate ? 'true' : 'false' ?>">
                <div class="plugin-header">
                    <div class="plugin-title-container">
                        <h3 class="plugin-title">
                            <?= htmlspecialchars($plugin->getName()) ?>
                            <?php if ($pluginName === 'TestPlugin'): ?>
                                <i class="fas fa-star" style="color: var(--warning);" title="Рекомендуемый плагин"></i>
                            <?php endif; ?>
                        </h3>
                        <div class="plugin-version">v<?= htmlspecialchars($plugin->getVersion()) ?></div>
                    </div>
                    <span class="plugin-status status-<?= $isActive ? 'active' : 'inactive' ?>">
                            <?= $isActive ? 'Активен' : 'Неактивен' ?>
                        </span>
                </div>

                <div class="plugin-body">
                    <p class="plugin-description"><?= htmlspecialchars($plugin->getDescription()) ?></p>

                    <?php if ($hasUpdate): ?>
                        <div class="update-available">
                            <i class="fas fa-arrow-circle-up"></i>
                            Доступно обновление до версии 2.1.0
                        </div>
                    <?php endif; ?>

                    <div class="plugin-details">
                        <div class="detail-item">
                            <span class="detail-label">Совместимость:</span>
                            <span class="detail-value">v1.2+</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Последнее обновление:</span>
                            <span class="detail-value">2 недели назад</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Рейтинг:</span>
                            <span class="detail-value">
                                    <i class="fas fa-star" style="color: var(--warning);"></i>
                                    <i class="fas fa-star" style="color: var(--warning);"></i>
                                    <i class="fas fa-star" style="color: var(--warning);"></i>
                                    <i class="fas fa-star" style="color: var(--warning);"></i>
                                    <i class="fas fa-star-half-alt" style="color: var(--warning);"></i>
                                    4.5
                                </span>
                        </div>
                    </div>

                    <div class="plugin-meta">
                            <span class="plugin-author">
                                <i class="fas fa-user"></i>
                                <?= htmlspecialchars($plugin->getAuthor()) ?>
                            </span>
                        <?php if (method_exists($plugin, 'getWebsite') && $plugin->getWebsite()): ?>
                            <a href="<?= htmlspecialchars($plugin->getWebsite()) ?>" target="_blank">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="plugin-actions">
                    <?php if ($isActive): ?>
                        <a href="/admin/plugins/deactivate/<?= urlencode($pluginName) ?>?v=<?= time() ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Вы уверены, что хотите деактивировать этот плагин?')">
                            <i class="fas fa-power-off"></i> Деактивировать
                        </a>
                        <?php if (method_exists($plugin, 'getSettingsUrl') && $plugin->getSettingsUrl()): ?>
                            <a href="<?= $plugin->getSettingsUrl() ?>" class="btn btn-secondary btn-sm">
                                <i class="fas fa-cog"></i> Настройки
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="/admin/plugins/activate/<?= urlencode($pluginName) ?>?v=<?= time() ?>"
                           class="btn btn-primary btn-sm"
                           onclick="return confirm('Вы уверены, что хотите активировать этот плагин?')">
                            <i class="fas fa-plug"></i> Активировать
                        </a>
                    <?php endif; ?>

                    <?php if ($hasUpdate): ?>
                        <button class="btn btn-success btn-sm update-plugin-btn" data-plugin="<?= htmlspecialchars($pluginName) ?>">
                            <i class="fas fa-download"></i> Обновить
                        </button>
                    <?php endif; ?>

                    <button class="btn btn-secondary btn-sm plugin-info-btn" data-plugin="<?= htmlspecialchars($pluginName) ?>">
                        <i class="fas fa-info-circle"></i> Подробнее
                    </button>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Если плагинов нет -->
        <?php if (empty($plugins)): ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h3>Плагины не найдены</h3>
                <p>В системе не обнаружено установленных плагинов.</p>
                <button class="btn btn-primary" id="uploadPluginEmptyBtn" style="margin-top: 1rem;">
                    <i class="fas fa-upload"></i> Загрузить первый плагин
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Модальное окно загрузки плагина -->
<div class="modal-overlay" id="uploadModal">
    <div class="modal">
        <div class="modal-header">
            <h2 class="modal-title">Загрузка плагина</h2>
            <button class="modal-close" id="closeUploadModal">&times;</button>
        </div>
        <div class="modal-body">
            <form class="modal-form" id="uploadPluginForm">
                <div class="form-group">
                    <label class="form-label">Файл плагина (ZIP)</label>
                    <div class="form-file" id="fileDropArea">
                        <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; margin-bottom: 0.5rem; color: var(--gray-400);"></i>
                        <p>Перетащите файл сюда или нажмите для выбора</p>
                        <input type="file" id="pluginFile" accept=".zip" style="display: none;">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Или URL плагина</label>
                    <input type="url" class="form-input" placeholder="https://example.com/plugin.zip" id="pluginUrl">
                </div>
                <div class="form-group">
                    <label class="form-label">Дополнительная информация (необязательно)</label>
                    <textarea class="form-input form-textarea" placeholder="Описание или заметки о плагине..." id="pluginNotes"></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="cancelUpload">Отмена</button>
            <button class="btn btn-primary" id="submitUpload">Загрузить</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Элементы модального окна
        const uploadModal = document.getElementById('uploadModal');
        const uploadPluginBtn = document.getElementById('uploadPluginBtn');
        const uploadPluginEmptyBtn = document.getElementById('uploadPluginEmptyBtn');
        const closeUploadModal = document.getElementById('closeUploadModal');
        const cancelUpload = document.getElementById('cancelUpload');
        const fileDropArea = document.getElementById('fileDropArea');
        const pluginFile = document.getElementById('pluginFile');
        const submitUpload = document.getElementById('submitUpload');

        // Открытие модального окна
        function openUploadModal() {
            uploadModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        // Закрытие модального окна
        function closeUploadModalFunc() {
            uploadModal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        // Обработчики событий для модального окна
        if (uploadPluginBtn) {
            uploadPluginBtn.addEventListener('click', openUploadModal);
        }

        if (uploadPluginEmptyBtn) {
            uploadPluginEmptyBtn.addEventListener('click', openUploadModal);
        }

        if (closeUploadModal) {
            closeUploadModal.addEventListener('click', closeUploadModalFunc);
        }

        if (cancelUpload) {
            cancelUpload.addEventListener('click', closeUploadModalFunc);
        }

        // Закрытие модального окна при клике вне его области
        uploadModal.addEventListener('click', function(e) {
            if (e.target === uploadModal) {
                closeUploadModalFunc();
            }
        });

        // Drag and drop для загрузки файлов
        if (fileDropArea) {
            fileDropArea.addEventListener('click', function() {
                pluginFile.click();
            });

            fileDropArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                fileDropArea.style.borderColor = 'var(--primary)';
                fileDropArea.style.background = 'var(--primary-light)';
            });

            fileDropArea.addEventListener('dragleave', function() {
                fileDropArea.style.borderColor = 'var(--gray-300)';
                fileDropArea.style.background = 'white';
            });

            fileDropArea.addEventListener('drop', function(e) {
                e.preventDefault();
                fileDropArea.style.borderColor = 'var(--gray-300)';
                fileDropArea.style.background = 'white';

                if (e.dataTransfer.files.length) {
                    pluginFile.files = e.dataTransfer.files;
                    fileDropArea.querySelector('p').textContent = `Выбран файл: ${e.dataTransfer.files[0].name}`;
                }
            });
        }

        // Обработка выбора файла
        if (pluginFile) {
            pluginFile.addEventListener('change', function() {
                if (pluginFile.files.length) {
                    fileDropArea.querySelector('p').textContent = `Выбран файл: ${pluginFile.files[0].name}`;
                }
            });
        }

        // Отправка формы
        if (submitUpload) {
            submitUpload.addEventListener('click', function() {
                alert('Функция загрузки плагина будет реализована в backend части');
                closeUploadModalFunc();
            });
        }

        // Фильтрация по статусу
        const filterTabs = document.querySelectorAll('.filter-tab');
        const pluginCards = document.querySelectorAll('.plugin-card');
        const searchInput = document.getElementById('pluginSearch');

        // Обработка фильтров
        filterTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const filter = this.getAttribute('data-filter');

                // Активируем вкладку
                filterTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                // Фильтруем плагины
                pluginCards.forEach(card => {
                    const status = card.getAttribute('data-status');
                    const hasUpdate = card.getAttribute('data-update') === 'true';
                    let isVisible = false;

                    if (filter === 'all') isVisible = true;
                    else if (filter === 'active') isVisible = status === 'active';
                    else if (filter === 'inactive') isVisible = status === 'inactive';
                    else if (filter === 'updates') isVisible = hasUpdate;

                    card.style.display = isVisible ? 'block' : 'none';
                });
            });
        });

        // Поиск плагинов
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchText = this.value.toLowerCase();

                pluginCards.forEach(card => {
                    const pluginName = card.querySelector('.plugin-title').textContent.toLowerCase();
                    const pluginDesc = card.querySelector('.plugin-description').textContent.toLowerCase();

                    if (pluginName.includes(searchText) || pluginDesc.includes(searchText)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        }

        // Проверка обновлений
        const checkUpdatesBtn = document.getElementById('checkUpdatesBtn');
        if (checkUpdatesBtn) {
            checkUpdatesBtn.addEventListener('click', function() {
                this.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Проверка...';
                this.disabled = true;

                // Имитация проверки обновлений
                setTimeout(() => {
                    this.innerHTML = '<i class="fas fa-sync-alt"></i> Проверить обновления';
                    this.disabled = false;
                    alert('Проверка завершена. Доступно 2 обновления.');
                }, 2000);
            });
        }

        // Кнопки обновления плагинов
        const updateButtons = document.querySelectorAll('.update-plugin-btn');
        updateButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const pluginName = this.getAttribute('data-plugin');
                this.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Обновление...';
                this.disabled = true;

                // Имитация обновления плагина
                setTimeout(() => {
                    this.innerHTML = '<i class="fas fa-check"></i> Обновлено';
                    this.classList.remove('btn-success');
                    this.classList.add('btn-secondary');
                    alert(`Плагин "${pluginName}" успешно обновлен до версии 2.1.0`);
                }, 1500);
            });
        });

        // Кнопки подробной информации
        const infoButtons = document.querySelectorAll('.plugin-info-btn');
        infoButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const pluginName = this.getAttribute('data-plugin');
                alert(`Детальная информация о плагине "${pluginName}" будет отображена здесь.`);
            });
        });
    });
</script>
</body>
</html>
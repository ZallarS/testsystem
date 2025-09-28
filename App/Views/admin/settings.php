<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-cog me-2"></i>Основные настройки
                </h5>
            </div>
            <div class="card-body">
                <form>
                    <div class="mb-3">
                        <label for="siteName" class="form-label">Название сайта</label>
                        <input type="text" class="form-control" id="siteName" value="Система тестирований">
                    </div>
                    <div class="mb-3">
                        <label for="timezone" class="form-label">Часовой пояс</label>
                        <select class="form-select" id="timezone">
                            <option value="Europe/Moscow" selected>Europe/Moscow (Москва)</option>
                            <option value="Europe/London">Europe/London (Лондон)</option>
                            <option value="America/New_York">America/New_York (Нью-Йорк)</option>
                        </select>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="maintenanceMode">
                        <label class="form-check-label" for="maintenanceMode">Режим технического обслуживания</label>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Сохранить настройки
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-info-circle me-2"></i>Информация о системе
                </h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Версия PHP
                        <span class="badge bg-primary rounded-pill"><?= phpversion() ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Версия MySQL
                        <span class="badge bg-primary rounded-pill"><?= phpversion() ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        ОС сервера
                        <span class="badge bg-primary rounded-pill"><?= php_uname('s') ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Макс. размер загрузки
                        <span class="badge bg-primary rounded-pill"><?= ini_get('upload_max_filesize') ?></span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>Опасная зона
                </h5>
            </div>
            <div class="card-body">
                <p class="card-text">Эти действия нельзя отменить. Будьте осторожны.</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-danger" type="button">
                        <i class="fas fa-trash me-2"></i> Очистить кеш
                    </button>
                    <button class="btn btn-outline-danger" type="button">
                        <i class="fas fa-database me-2"></i> Очистить логи
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
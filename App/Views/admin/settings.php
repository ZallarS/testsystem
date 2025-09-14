<?php

?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Настройки системы</h1>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Основные настройки</h5>
            </div>
            <div class="card-body">
                <form>
                    <div class="mb-3">
                        <label for="siteName" class="form-label">Название сайта</label>
                        <input type="text" class="form-control" id="siteName" value="Система тестирований">
                    </div>
                    <div class="mb-3">
                        <label for="adminEmail" class="form-label">Email администратора</label>
                        <input type="email" class="form-control" id="adminEmail" value="admin@example.com">
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
                    <button type="submit" class="btn btn-primary">Сохранить настройки</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Информация о системе</h5>
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
                <h5 class="card-title">Опасная зона</h5>
            </div>
            <div class="card-body">
                <p class="card-text">Эти действия нельзя отменить. Будьте осторожны.</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-danger" type="button">
                        <i class="bi bi-trash"></i> Очистить кеш
                    </button>
                    <button class="btn btn-outline-danger" type="button">
                        <i class="bi bi-database"></i> Очистить логи
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
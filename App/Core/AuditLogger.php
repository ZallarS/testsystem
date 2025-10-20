<?php

    namespace App\Core;

    class AuditLogger
    {
        private static $logPath;
        private static $initialized = false;
        private static $enabled = true;

        public static function initialize()
        {
            if (self::$initialized) {
                return self::$enabled;
            }

            self::$logPath = STORAGE_PATH . '/logs/audit';

            // Создаем директорию если её нет
            if (!is_dir(self::$logPath)) {
                if (!@mkdir(self::$logPath, 0755, true)) {
                    error_log("AuditLogger: Failed to create directory, disabling audit logging");
                    self::$enabled = false;
                    self::$initialized = true;
                    return false;
                }
            }

            // Проверяем права на запись (но не пытаемся изменить права)
            if (!is_writable(self::$logPath)) {
                error_log("AuditLogger: Directory is not writable, disabling audit logging: " . self::$logPath);
                self::$enabled = false;
                self::$initialized = true;
                return false;
            }

            self::$initialized = true;
            return true;
        }

        public static function log($action, $details = [], $userId = null)
        {
            // Если аудит отключен, просто возвращаем true
            if (!self::$enabled) {
                return true;
            }

            // Инициализируем если нужно
            if (!self::initialize()) {
                return false;
            }

            $userId = $userId ?: (User::getId() ?? 'unknown');
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

            $logEntry = [
                'timestamp' => date('Y-m-d H:i:s'),
                'user_id' => $userId,
                'action' => $action,
                'ip' => $ip,
                'user_agent' => $userAgent,
                'details' => $details
            ];

            $logFile = self::$logPath . '/audit-' . date('Y-m-d') . '.log';
            $logMessage = json_encode($logEntry) . PHP_EOL;

            // Пытаемся записать в файл с обработкой ошибок
            $result = @error_log($logMessage, 3, $logFile);

            if (!$result) {
                // Fallback: записываем в системный лог без префикса файла
                error_log("AUDIT: " . $logMessage);
                return false;
            }

            return true;
        }

        public static function logLogin($userId, $email, $success = true)
        {
            return self::log('login', [
                'email' => $email,
                'success' => $success
            ], $userId);
        }

        public static function logLogout($userId)
        {
            return self::log('logout', [], $userId);
        }

        public static function logUserCreation($adminId, $newUserId, $userData)
        {
            return self::log('user_creation', [
                'created_user_id' => $newUserId,
                'user_data' => $userData
            ], $adminId);
        }

        public static function logUserUpdate($adminId, $targetUserId, $changes)
        {
            return self::log('user_update', [
                'target_user_id' => $targetUserId,
                'changes' => $changes
            ], $adminId);
        }

        public static function logPermissionChange($adminId, $targetUserId, $oldRoles, $newRoles)
        {
            return self::log('permission_change', [
                'target_user_id' => $targetUserId,
                'old_roles' => $oldRoles,
                'new_roles' => $newRoles
            ], $adminId);
        }

        // Метод для принудительного включения/отключения аудита
        public static function setEnabled($enabled)
        {
            self::$enabled = $enabled;
        }
    }
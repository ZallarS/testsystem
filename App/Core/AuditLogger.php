<?php

    namespace App\Core;

    class AuditLogger
    {
        private static $logPath;

        public static function initialize()
        {
            self::$logPath = STORAGE_PATH . '/logs/audit';
            if (!is_dir(self::$logPath)) {
                mkdir(self::$logPath, 0755, true);
            }
        }

        public static function log($action, $details = [], $userId = null)
        {
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

            error_log($logMessage, 3, $logFile);
        }

        public static function logLogin($userId, $email, $success = true)
        {
            self::log('login', [
                'email' => $email,
                'success' => $success
            ], $userId);
        }

        public static function logLogout($userId)
        {
            self::log('logout', [], $userId);
        }

        public static function logUserCreation($adminId, $newUserId, $userData)
        {
            self::log('user_creation', [
                'created_user_id' => $newUserId,
                'user_data' => $userData
            ], $adminId);
        }

        public static function logUserUpdate($adminId, $targetUserId, $changes)
        {
            self::log('user_update', [
                'target_user_id' => $targetUserId,
                'changes' => $changes
            ], $adminId);
        }

        public static function logPermissionChange($adminId, $targetUserId, $oldRoles, $newRoles)
        {
            self::log('permission_change', [
                'target_user_id' => $targetUserId,
                'old_roles' => $oldRoles,
                'new_roles' => $newRoles
            ], $adminId);
        }
    }
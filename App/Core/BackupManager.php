<?php

    namespace App\Core;

    class BackupManager
    {
        private $backupPath;
        private $db;

        public function __construct()
        {
            $this->backupPath = STORAGE_PATH . '/backups';
            $this->db = Database\Connection::getInstance()->getPdo();

            if (!is_dir($this->backupPath)) {
                mkdir($this->backupPath, 0755, true);
            }
        }

        public function createDatabaseBackup()
        {
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "backup_{$timestamp}.sql";
            $filepath = $this->backupPath . '/' . $filename;

            // Get database configuration
            $dbName = $_ENV['DB_DATABASE'];
            $dbHost = $_ENV['DB_HOST'];
            $dbUser = $_ENV['DB_USERNAME'];
            $dbPass = $_ENV['DB_PASSWORD'];

            // Create backup using mysqldump (if available)
            $command = "mysqldump --host={$dbHost} --user={$dbUser} --password={$dbPass} {$dbName} > {$filepath}";

            system($command, $result);

            if ($result !== 0) {
                throw new \Exception('Database backup failed');
            }

            // Compress the backup
            $this->compressFile($filepath);

            // Clean old backups (keep only last 7 days)
            $this->cleanOldBackups();

            return $filename . '.gz';
        }

        public function createApplicationBackup()
        {
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "app_backup_{$timestamp}.tar.gz";
            $filepath = $this->backupPath . '/' . $filename;

            $directories = [
                APP_PATH,
                BASE_PATH . '/config',
                STORAGE_PATH . '/logs'
            ];

            $command = "tar -czf {$filepath} " . implode(' ', $directories);
            system($command, $result);

            if ($result !== 0) {
                throw new \Exception('Application backup failed');
            }

            return $filename;
        }

        private function compressFile($filepath)
        {
            $compressedFile = $filepath . '.gz';

            $data = file_get_contents($filepath);
            $compressed = gzencode($data, 9);

            file_put_contents($compressedFile, $compressed);
            unlink($filepath);
        }

        private function cleanOldBackups()
        {
            $files = glob($this->backupPath . '/*.gz');
            $now = time();
            $daysToKeep = 7;

            foreach ($files as $file) {
                if (is_file($file)) {
                    if ($now - filemtime($file) >= $daysToKeep * 24 * 60 * 60) {
                        unlink($file);
                    }
                }
            }
        }

        public function getBackupList()
        {
            $files = glob($this->backupPath . '/*.gz');
            $backups = [];

            foreach ($files as $file) {
                $backups[] = [
                    'filename' => basename($file),
                    'size' => filesize($file),
                    'created' => date('Y-m-d H:i:s', filemtime($file))
                ];
            }

            return $backups;
        }

        public function restoreBackup($filename)
        {
            $filepath = $this->backupPath . '/' . $filename;

            if (!file_exists($filepath)) {
                throw new \Exception('Backup file not found');
            }

            // Implementation for restore would go here
            // This is a complex operation and should be handled carefully

            return true;
        }
    }
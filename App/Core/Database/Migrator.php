<?php

    namespace App\Core\Database;

    use App\Core\Database\Connection;
    use Exception;
    use PDO;

    class Migrator
    {
        private $db;
        private $migrationsPath;
        private $migrationsTable = 'migrations';

        public function __construct()
        {
            $this->db = Connection::getInstance()->getPdo();
            $this->migrationsPath = MIGRATIONS_PATH;
        }

        public function getDb()
        {
            return $this->db;
        }

        public function run()
        {
            $this->createMigrationsTable();

            $appliedMigrations = $this->getAppliedMigrations();
            $migrationFiles = $this->getMigrationFiles();

            $migrationsToApply = array_diff($migrationFiles, $appliedMigrations);

            if (empty($migrationsToApply)) {
                echo "Новые миграции не применились.\n";
                return;
            }

            $batch = $this->getNextBatchNumber();

            foreach ($migrationsToApply as $migration) {
                $this->applyMigration($migration, $batch);
            }

            echo "Все миграции были успешно выполнены.\n";
        }

        public function create($name, $options = [])
        {
            $timestamp = date('Y_m_d_His');
            $filename = $timestamp . '_' . $this->toSnakeCase($name) . '.php';
            $filepath = $this->migrationsPath . '/' . $filename;

            $className = $this->toCamelCase($name);
            $template = $this->getMigrationTemplate($className, $options);

            if (file_put_contents($filepath, $template)) {
                echo "Создана миграция: $filename\n";
                return $filename;
            } else {
                throw new Exception("Ошибка создания миграции в файле: $filepath");
            }
        }

        public function reset()
        {
            $appliedMigrations = array_reverse($this->getAppliedMigrations());

            foreach ($appliedMigrations as $migration) {
                $this->rollbackMigration($migration);
            }

            echo "Сброс базы данных успешен.\n";
        }
        public function rollback($steps = 1)
        {
            $appliedMigrations = array_reverse($this->getAppliedMigrations());
            $migrationsToRollback = array_slice($appliedMigrations, 0, $steps);

            foreach ($migrationsToRollback as $migration) {
                $this->rollbackMigration($migration);
            }

            echo "Откат миграций успешно выполнен.\n";
        }

        public function refresh()
        {
            $this->reset();
            $this->run();
            echo "База данных пересоздана.\n";
        }

        private function applyMigration($migration, $batch) {
            require_once $this->migrationsPath . '/' . $migration;

            $className = $this->getClassNameFromFilename($migration);

            if (!class_exists($className)) {
                throw new \Exception("Класс $className Не найден в миграционом файле: $migration");
            }

            // Устанавливаем соединение для Schema
            Schema::setConnection($this->db);

            $instance = new $className();

            try {
                // Начинаем транзакцию для безопасного выполнения миграции
                $this->db->beginTransaction();

                echo "Применение миграции: $migration\n";
                $instance->up();
                $this->recordMigration($migration, $batch);

                $this->db->commit();
                echo "Миграция применена: $migration\n";

            } catch (Exception $e) {
                $this->db->rollBack();
                echo "Ошибка применения миграции $migration: " . $e->getMessage() . "\n";
                throw new Exception("Миграция не применена: " . $e->getMessage());
            }
        }

        private function rollbackMigration($migration)
        {
            require_once $this->migrationsPath . '/' . $migration;

            $className = $this->getClassNameFromFilename($migration);

            if (!class_exists($className)) {
                throw new Exception("Класс $className не найден в миграционном файле: $migration");
            }

            // Проверяем, что класс наследует базовый класс миграции
            if (!is_subclass_of($className, 'App\Core\Database\Migration')) {
                throw new Exception("Migration class $className must extend App\Core\Database\Migration");
            }

            $instance = new $className();

            // Устанавливаем соединение для Schema
            Schema::setConnection($this->db); // Добавьте эту строку

            try {
                echo "Откат миграции: $migration\n";
                $instance->down($this->db); // Если down() принимает соединение, иначе уберите аргумент

                $this->removeMigrationRecord($migration);
                echo "Успешно откатилась миграция: $migration\n";

            } catch (Exception $e) {
                echo "Ошибка отката миграции $migration: " . $e->getMessage() . "\n";
                throw new Exception("Не удалось откатить миграцию: " . $e->getMessage());
            }
        }

        private function createMigrationsTable()
        {
            $checkTable = $this->db->query("SHOW TABLES LIKE '{$this->migrationsTable}'")->fetch(PDO::FETCH_ASSOC);

            if (!$checkTable) {
                $sql = "CREATE TABLE {$this->migrationsTable} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
                $this->db->exec($sql);
            }
        }

        private function getAppliedMigrations()
        {
            try {
                $stmt = $this->db->query("SELECT migration FROM {$this->migrationsTable} ORDER BY id");
                return $stmt->fetchAll(PDO::FETCH_COLUMN); // Теперь PDO распознается
            } catch (Exception $e) {
                // Если таблицы миграций не существует, возвращаем пустой массив
                return [];
            }
        }

        private function getMigrationFiles()
        {
            $files = scandir($this->migrationsPath);
            return array_filter($files, function ($file) {
                return preg_match('/^\d+_.+\.php$/', $file);
            });
        }

        private function getNextBatchNumber()
        {
            $stmt = $this->db->query("SELECT MAX(batch) FROM {$this->migrationsTable}");
            $maxBatch = $stmt->fetch(PDO::FETCH_COLUMN); // Добавляем PDO::
            return $maxBatch ? $maxBatch + 1 : 1;
        }

        private function recordMigration($migration, $batch)
        {
            $sql = "INSERT INTO {$this->migrationsTable} (migration, batch) VALUES (?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$migration, $batch]);
        }

        private function removeMigrationRecord($migration)
        {
            $sql = "DELETE FROM {$this->migrationsTable} WHERE migration = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$migration]);
        }

        private function getClassNameFromFilename($filename)
        {
            $name = pathinfo($filename, PATHINFO_FILENAME);

            // Удаляем всю временную метку (все цифры и подчеркивания в начале)
            $name = preg_replace('/^\d+(_\d+)*_/', '', $name);

            // Преобразуем snake_case в CamelCase
            return $this->toCamelCase($name);
        }

        private function toSnakeCase($string)
        {
            // Преобразуем CamelCase в snake_case
            return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
        }

        private function toCamelCase($string)
        {
            // Заменяем подчеркивания на пробелы, делаем слова с заглавной буквы и убираем пробелы
            return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
        }

        private function getMigrationTemplate($className, $options = [])
        {
            $tableName = $options['create'] ?? $options['table'] ?? 'table_name';
            $isCreate = isset($options['create']);
            $isTable = isset($options['table']);

            if ($isCreate) {
                $up = "Schema::create('$tableName', function(\$table) {\n            \$table->id();\n            \$table->timestamps();\n        });";
                $down = "Schema::dropIfExists('$tableName');";
            } elseif ($isTable) {
                $up = "Schema::table('$tableName', function(\$table) {\n            // \$table->string('new_column');\n        });";
                $down = "Schema::table('$tableName', function(\$table) {\n            // \$table->dropColumn('new_column');\n        });";
            } else {
                $up = "// Код миграции";
                $down = "// Код отката";
            }

            return "<?php

use App\Core\Database\Schema;

class {$className} {
    public function up() {
        $up
    }

    public function down() {
        $down
    }
}";
        }
    }
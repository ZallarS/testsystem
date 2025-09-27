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

    public function run()
    {
        $this->createMigrationsTable();

        $appliedMigrations = $this->getAppliedMigrations();
        $migrationFiles = $this->getMigrationFiles();

        $migrationsToApply = array_diff($migrationFiles, $appliedMigrations);

        if (empty($migrationsToApply)) {
            echo "Нет новых миграций для применения.\n";
            return;
        }

        $batch = $this->getNextBatchNumber();

        foreach ($migrationsToApply as $migration) {
            $this->applyMigration($migration, $batch);
        }

        echo "Все миграции успешно применены.\n";
    }

    private function applyMigration($migration, $batch)
    {
        require_once $this->migrationsPath . '/' . $migration;

        $className = $this->getClassNameFromFilename($migration);

        if (!class_exists($className)) {
            throw new \Exception("Класс $className не найден в файле миграции: $migration");
        }

        // Устанавливаем соединение для Schema
        Schema::setConnection($this->db);

        $instance = new $className();

        try {
            echo "Применение миграции: $migration\n";

            // Для миграции создания таблицы migrations проверяем существование
            if ($migration === '2025_09_27_130000_create_migrations_table.php') {
                $tableExists = $this->db->query("SHOW TABLES LIKE 'migrations'")->rowCount() > 0;
                if (!$tableExists) {
                    $instance->up();
                    echo "Таблица migrations создана\n";
                } else {
                    echo "Таблица migrations уже существует, пропускаем создание\n";
                }
            } else {
                // Для остальных миграций просто выполняем
                $instance->up();
            }

            // Записываем миграцию в таблицу
            $this->recordMigration($migration, $batch);
            echo "✓ Миграция успешно применена: $migration\n";

        } catch (Exception $e) {
            echo "✗ Ошибка применения миграции $migration: " . $e->getMessage() . "\n";

            // Показываем более детальную информацию об ошибке
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo "  Подсказка: Таблица уже существует. Пропускаем создание.\n";
                // Все равно записываем миграцию как примененную
                $this->recordMigration($migration, $batch);
                echo "✓ Миграция записана (таблица уже существует): $migration\n";
            } else {
                throw new Exception("Миграция не применена: " . $e->getMessage());
            }
        }
    }

    private function createMigrationsTable()
    {
        // Проверяем существование таблицы migrations без вызова SHOW TABLES
        // чтобы избежать ошибок если таблицы не существует
        try {
            $this->db->query("SELECT 1 FROM {$this->migrationsTable} LIMIT 1");
        } catch (Exception $e) {
            // Таблица не существует, создаем ее
            $sql = "CREATE TABLE {$this->migrationsTable} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                batch INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

            $this->db->exec($sql);
            echo "Таблица migrations создана\n";
        }
    }

    private function getAppliedMigrations()
    {
        try {
            $stmt = $this->db->query("SELECT migration FROM {$this->migrationsTable} ORDER BY id");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
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
        try {
            $stmt = $this->db->query("SELECT MAX(batch) FROM {$this->migrationsTable}");
            $maxBatch = $stmt->fetch(PDO::FETCH_COLUMN);
            return $maxBatch ? $maxBatch + 1 : 1;
        } catch (Exception $e) {
            return 1;
        }
    }

    private function recordMigration($migration, $batch)
    {
        $sql = "INSERT INTO {$this->migrationsTable} (migration, batch) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$migration, $batch]);
    }

    private function getClassNameFromFilename($filename)
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $name = preg_replace('/^\d+(_\d+)*_/', '', $name);
        return $this->toCamelCase($name);
    }

    private function toCamelCase($string)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }

    // Остальные методы (create, reset, rollback, refresh) остаются без изменений
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

    private function rollbackMigration($migration)
    {
        require_once $this->migrationsPath . '/' . $migration;

        $className = $this->getClassNameFromFilename($migration);

        if (!class_exists($className)) {
            throw new Exception("Класс $className не найден в миграционном файле: $migration");
        }

        Schema::setConnection($this->db);
        $instance = new $className();

        try {
            echo "Откат миграции: $migration\n";
            $instance->down();
            $this->removeMigrationRecord($migration);
            echo "Успешно откатилась миграция: $migration\n";
        } catch (Exception $e) {
            echo "Ошибка отката миграции $migration: " . $e->getMessage() . "\n";
        }
    }

    private function removeMigrationRecord($migration)
    {
        $sql = "DELETE FROM {$this->migrationsTable} WHERE migration = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$migration]);
    }

    private function toSnakeCase($string)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
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
        {$up}
    }

    public function down() {
        {$down}
    }
}";
    }
}
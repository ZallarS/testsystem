<?php

    namespace App\Core\Database;

    class Blueprint
    {
        private $tableName;
        private $columns = [];
        private $alterColumns = [];
        private $isAlter = false;
        private $indexes = [];
        private $foreignKeys = [];

        public function __construct($tableName, $isAlter = false)
        {
            $this->tableName = $tableName;
            $this->isAlter = $isAlter;
        }

        public function primary($columns, $name = null)
        {
            if (is_string($columns)) {
                $columns = [$columns];
            }

            $name = $name ?: $this->tableName . '_' . implode('_', $columns) . '_primary';
            $this->indexes[] = "PRIMARY KEY `{$name}` (" . implode(', ', array_map(function($col) {
                    return "`{$col}`";
                }, $columns)) . ")";

            return $this;
        }

        public function dropPrimary($indexName = null)
        {
            $indexName = $indexName ?: 'PRIMARY';
            $this->alterColumns[] = "DROP PRIMARY KEY `{$indexName}`";
            return $this;
        }

        public function id($name = 'id')
        {
            return $this->bigIncrements($name);
        }

        public function string($name, $length = 255)
        {
            $column = new Column($name, 'VARCHAR', $length);
            $this->columns[] = $column;
            return $column;
        }

        public function text($name)
        {
            $column = new Column($name, 'TEXT');
            $this->columns[] = $column;
            return $column;
        }

        public function integer($name)
        {
            $column = new Column($name, 'INT');
            $this->columns[] = $column;
            return $column;
        }

        public function bigInteger($name)
        {
            $column = new Column($name, 'BIGINT');
            $this->columns[] = $column;
            return $column;
        }

        public function boolean($name)
        {
            $column = new Column($name, 'TINYINT', 1);
            $this->columns[] = $column;
            return $column;
        }

        public function decimal($name, $total = 8, $places = 2)
        {
            $column = new Column($name, 'DECIMAL', "{$total},{$places}");
            $this->columns[] = $column;
            return $column;
        }

        public function date($name)
        {
            $column = new Column($name, 'DATE');
            $this->columns[] = $column;
            return $column;
        }

        public function dateTime($name)
        {
            $column = new Column($name, 'DATETIME');
            $this->columns[] = $column;
            return $column;
        }

        public function timestamp($name)
        {
            $column = new Column($name, 'TIMESTAMP');
            $this->columns[] = $column;
            return $column;
        }

        public function timestamps()
        {
            $this->timestamp('created_at')->nullable();
            $this->timestamp('updated_at')->nullable();
            return $this;
        }

        public function unsignedBigInteger($name)
        {
            $column = new Column($name, 'BIGINT');
            $column->unsigned();
            $this->columns[] = $column;
            return $column;
        }

        public function bigIncrements($name = 'id')
        {
            $column = new Column($name, 'BIGINT');
            $column->primary()->autoIncrement()->unsigned();
            $this->columns[] = $column;
            return $this;
        }

        public function index($columns, $indexName = null)
        {
            if (is_string($columns)) {
                $columns = [$columns];
            }
            $indexName = $indexName ?: $this->tableName . '_' . implode('_', $columns) . '_index';
            $this->indexes[] = "INDEX `{$indexName}` (" . implode(', ', array_map(function($col) { return "`{$col}`"; }, $columns)) . ")";
            return $this;
        }

        public function unique($columns, $indexName = null)
        {
            if (is_string($columns)) {
                $columns = [$columns];
            }
            $indexName = $indexName ?: $this->tableName . '_' . implode('_', $columns) . '_unique';
            $this->indexes[] = "UNIQUE `{$indexName}` (" . implode(', ', array_map(function($col) { return "`{$col}`"; }, $columns)) . ")";
            return $this;
        }

        public function foreign($column, $indexName = null)
        {
            $indexName = $indexName ?: $this->tableName . '_' . $column . '_foreign';
            $this->foreignKeys[] = [
                'column' => $column,
                'indexName' => $indexName
            ];
            return $this;
        }

        public function references($columnOnOtherTable)
        {
            $lastForeignKey = end($this->foreignKeys);
            if ($lastForeignKey) {
                $key = key($this->foreignKeys);
                $this->foreignKeys[$key]['references'] = $columnOnOtherTable;
            }
            return $this;
        }

        public function on($tableName)
        {
            $lastForeignKey = end($this->foreignKeys);
            if ($lastForeignKey) {
                $key = key($this->foreignKeys);
                $this->foreignKeys[$key]['on'] = $tableName;
            }
            return $this;
        }

        public function onDelete($action)
        {
            $lastForeignKey = end($this->foreignKeys);
            if ($lastForeignKey) {
                $key = key($this->foreignKeys);
                $this->foreignKeys[$key]['onDelete'] = $action;
            }
            return $this;
        }

        public function onUpdate($action)
        {
            $lastForeignKey = end($this->foreignKeys);
            if ($lastForeignKey) {
                $key = key($this->foreignKeys);
                $this->foreignKeys[$key]['onUpdate'] = $action;
            }
            return $this;
        }

        // Методы для ALTER TABLE
        public function addColumn($type, $name, $options = [])
        {
            $this->alterColumns[] = "ADD COLUMN `{$name}` {$type}" . (!empty($options) ? ' ' . implode(' ', $options) : '');
            return $this;
        }

        public function dropColumn($name)
        {
            $this->alterColumns[] = "DROP COLUMN `{$name}`";
            return $this;
        }

        public function modifyColumn($type, $name, $options = [])
        {
            $this->alterColumns[] = "MODIFY COLUMN `{$name}` {$type}" . (!empty($options) ? ' ' . implode(' ', $options) : '');
            return $this;
        }

        public function renameColumn($oldName, $newName)
        {
            $this->alterColumns[] = "CHANGE COLUMN `{$oldName}` `{$newName}`";
            return $this;
        }

        public function toSql()
        {
            $sql = '';
            if ($this->isAlter) {
                $sql = "ALTER TABLE `{$this->tableName}` " . implode(', ', $this->alterColumns);
            } else {
                $columnDefinitions = [];
                foreach ($this->columns as $column) {
                    $columnDefinitions[] = $column->toSql();
                }

                $indexes = !empty($this->indexes) ? ', ' . implode(', ', $this->indexes) : '';
                $foreignKeysSql = '';

                if (!empty($this->foreignKeys)) {
                    foreach ($this->foreignKeys as $fk) {
                        $foreignKeysSql .= ", CONSTRAINT `{$fk['indexName']}` FOREIGN KEY (`{$fk['column']}`) REFERENCES `{$fk['on']}`(`{$fk['references']}`)";
                        if (isset($fk['onDelete'])) {
                            $foreignKeysSql .= " ON DELETE {$fk['onDelete']}";
                        }
                        if (isset($fk['onUpdate'])) {
                            $foreignKeysSql .= " ON UPDATE {$fk['onUpdate']}";
                        }
                    }
                }

                $sql = "CREATE TABLE `{$this->tableName}` (" .
                    implode(', ', $columnDefinitions) .
                    $indexes .
                    $foreignKeysSql .
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            }
            return $sql;
        }
    }
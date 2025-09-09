<?php

    namespace App\Core\Database;

    class Column
    {
        public $name;
        public $type;
        public $length = null;
        public $nullable = false;
        public $default = null;
        public $primary = false;
        public $autoIncrement = false;
        public $unique = false;
        public $unsigned = false;
        public $index = false;

        public function __construct($name, $type, $length = null)
        {
            $this->name = $name;
            $this->type = $type;
            $this->length = $length;
        }

        public function unsigned()
        {
            $this->unsigned = true;
            return $this;
        }

        public function nullable()
        {
            $this->nullable = true;
            return $this;
        }

        public function default($value)
        {
            $this->default = $value;
            return $this;
        }

        public function primary()
        {
            $this->primary = true;
            return $this;
        }

        public function autoIncrement()
        {
            $this->autoIncrement = true;
            return $this;
        }

        public function unique()
        {
            $this->unique = true;
            return $this;
        }

        public function useCurrent()
        {
            $this->default = 'CURRENT_TIMESTAMP';
            return $this;
        }

        public function useCurrentOnUpdate()
        {
            // Для MySQL это отдельный синтаксис, обработаем его в toSql()
            $this->default = 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP';
            return $this;
        }

        public function toSql()
        {
            $sql = "`{$this->name}` {$this->type}";

            if ($this->length) {
                $sql .= "({$this->length})";
            }

            if ($this->unsigned) {
                $sql .= " UNSIGNED";
            }

            if (!$this->nullable) {
                $sql .= " NOT NULL";
            } else {
                $sql .= " NULL";
            }

            if ($this->default !== null) {
                // Для MySQL функций используем без кавычек
                if (in_array(strtoupper($this->default), ['CURRENT_TIMESTAMP', 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'])) {
                    $sql .= " DEFAULT {$this->default}";
                } else {
                    $sql .= " DEFAULT '" . addslashes($this->default) . "'";
                }
            }

            if ($this->autoIncrement) {
                $sql .= " AUTO_INCREMENT";
            }

            if ($this->primary) {
                $sql .= " PRIMARY KEY";
            }

            if ($this->unique) {
                $sql .= " UNIQUE";
            }

            if ($this->index) {
                $sql .= " INDEX";
            }

            return $sql;
        }
    }
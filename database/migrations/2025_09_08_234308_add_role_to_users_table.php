<?php

    use App\Core\Database\Schema;

    class AddRoleToUsersTable {
        public function up() {
            // Добавляем столбец role, если его еще нет
            if (!Schema::hasColumn('users', 'role')) {
                Schema::table('users', function($table) {
                    $table->addColumn('VARCHAR(255)', 'role', ['DEFAULT "user"', 'NOT NULL']);
                });
            }
        }

        public function down() {
            // Удаляем столбец role, если он существует
            if (Schema::hasColumn('users', 'role')) {
                Schema::table('users', function($table) {
                    $table->dropColumn('role');
                });
            }
        }
    }
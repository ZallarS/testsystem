<?php

    use App\Core\Database\Schema;

    class RemoveRoleFromUsersTable {
        public function up() {
            Schema::table('users', function($table) {
                $table->dropColumn('role');
            });
        }

        public function down() {
            Schema::table('users', function($table) {
                $table->string('role')->default('user');
            });
        }
    }
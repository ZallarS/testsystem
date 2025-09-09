<?php

    use App\Core\Database\Schema;

    class CreateUsersTable {
        public function up() {
            Schema::create('users', function($table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password');
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            });
        }

        public function down() {
            Schema::dropIfExists('users');
        }
    }
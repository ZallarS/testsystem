<?php

    use App\Core\Database\Schema;

    class CreateRolesTable {
        public function up() {
            Schema::create('roles', function($table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('description')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            });
        }

        public function down() {
            Schema::dropIfExists('roles');
        }
    }
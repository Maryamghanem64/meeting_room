<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('action_items')) {
            Schema::dropIfExists('action_items');
        }
        Schema::rename('assignments', 'action_items');
        Schema::table('action_items', function (Blueprint $table) {
            $table->renameColumn('action_item', 'description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('action_items', function (Blueprint $table) {
            $table->renameColumn('description', 'action_item');
        });
        Schema::rename('action_items', 'assignments');
    }
};

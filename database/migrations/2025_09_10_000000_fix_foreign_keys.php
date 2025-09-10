<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix foreign key for action_items.assigned_to
        Schema::table('action_items', function (Blueprint $table) {
            // Check if foreign key exists before dropping
            $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'action_items' AND COLUMN_NAME = 'assigned_to' AND REFERENCED_TABLE_NAME = 'users'");
            if (!empty($foreignKeys)) {
                $table->dropForeign(['assigned_to']);
            }
            // Add the correct foreign key
            $table->foreign('assigned_to')->references('Id')->on('users')->onDelete('cascade');
        });

        // Fix foreign key for meeting_minutes.meeting_id
        Schema::table('meeting_minutes', function (Blueprint $table) {
            // Check if foreign key exists before dropping
            $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'meeting_minutes' AND COLUMN_NAME = 'meeting_id' AND REFERENCED_TABLE_NAME = 'meetings'");
            if (!empty($foreignKeys)) {
                $table->dropForeign(['meeting_id']);
            }
            // Add the correct foreign key
            $table->foreign('meeting_id')->references('Id')->on('meetings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the changes
        Schema::table('action_items', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('meeting_minutes', function (Blueprint $table) {
            $table->dropForeign(['meeting_id']);
            $table->foreign('meeting_id')->references('id')->on('meetings')->onDelete('cascade');
        });
    }
};

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
        // Check if foreign key already exists
        $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'meeting_minutes' AND COLUMN_NAME = 'meeting_id' AND REFERENCED_TABLE_NAME = 'meetings'");
        if (empty($foreignKeys)) {
            Schema::table('meeting_minutes', function (Blueprint $table) {
                $table->foreign('meeting_id')->references('Id')->on('meetings')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meeting_minutes', function (Blueprint $table) {
            $table->dropForeign(['meeting_id']);
        });
    }
};

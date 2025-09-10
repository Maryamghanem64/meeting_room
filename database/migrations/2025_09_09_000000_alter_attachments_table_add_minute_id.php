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
        Schema::table('attachments', function (Blueprint $table) {
            $table->foreignId('minute_id')->nullable()->constrained('meeting_minutes', 'Id')->onDelete('cascade');
            $table->foreignId('meetingId')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropForeign(['minute_id']);
            $table->dropColumn('minute_id');
            $table->foreignId('meetingId')->nullable(false)->change();
        });
    }
};

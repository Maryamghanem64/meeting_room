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
        Schema::table('meeting_minutes', function (Blueprint $table) {
            if (Schema::hasColumn('meeting_minutes', 'meetingId')) {
                $table->renameColumn('meetingId', 'meeting_id');
            }
            if (!Schema::hasColumn('meeting_minutes', 'notes')) {
                $table->text('notes')->nullable()->after('meeting_id');
            }
            if (!Schema::hasColumn('meeting_minutes', 'decisions')) {
                $table->text('decisions')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('meeting_minutes', 'attachments')) {
                $table->json('attachments')->nullable()->after('decisions');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meeting_minutes', function (Blueprint $table) {
            if (Schema::hasColumn('meeting_minutes', 'attachments')) {
                $table->dropColumn('attachments');
            }
            if (Schema::hasColumn('meeting_minutes', 'decisions')) {
                $table->dropColumn('decisions');
            }
            if (Schema::hasColumn('meeting_minutes', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('meeting_minutes', 'meeting_id')) {
                $table->renameColumn('meeting_id', 'meetingId');
            }
        });
    }
};

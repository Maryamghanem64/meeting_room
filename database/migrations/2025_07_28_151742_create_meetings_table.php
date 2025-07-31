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
        Schema::create('meetings', function (Blueprint $table) {
            $table->id('Id');
        $table->foreignId('userId')->constrained('users')->onDelete('cascade');
        $table->foreignId('roomId')->constrained('rooms')->onDelete('cascade');
        $table->string('title')->nullable();
        $table->text('agenda')->nullable();
        $table->dateTime('startTime')->nullable();
        $table->dateTime('endTime')->nullable();
        $table->string('type')->nullable();
        $table->string('status')->default('pending');
        $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};

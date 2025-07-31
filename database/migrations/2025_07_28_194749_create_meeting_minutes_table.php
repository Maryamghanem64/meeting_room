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
        Schema::create('meeting_minutes', function (Blueprint $table) {
           $table->bigIncrements('Id');
        $table->unsignedBigInteger('featureId');
        $table->unsignedBigInteger('roomId');
        $table->timestamps();

        $table->foreign('featureId')->references('Id')->on('features')->onDelete('cascade');
        $table->foreign('roomId')->references('Id')->on('rooms')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_minutes');
    }
};

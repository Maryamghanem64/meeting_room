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
        Schema::create('feature_room', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('featureId')->constrained('features', 'Id')->onDelete('cascade');
            $table->foreignId('roomId')->constrained('rooms', 'Id')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_room');
    }
};

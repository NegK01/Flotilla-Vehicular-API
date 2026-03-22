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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('plate')->unique();
            $table->string('brand');
            $table->string('model');
            $table->integer('year');
            $table->string('vehicle_type');
            $table->integer('capacity');
            $table->string('fuel_type');
            $table->string('image_path');
            $table->enum('status', ['available', 'reserved', 'maintenance', 'out_of_service'])->default('available');
            $table->unsignedInteger('current_mileage')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};

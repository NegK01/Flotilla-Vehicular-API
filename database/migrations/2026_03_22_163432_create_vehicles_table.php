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
            $table->string('plate', 20)->unique();
            $table->string('brand', 100);
            $table->string('model', 100);
            $table->unsignedSmallInteger('year');
            $table->string('vehicle_type', 50);
            $table->unsignedTinyInteger('capacity');
            $table->string('fuel_type', 50);
            $table->string('image_path', 255)->nullable();
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

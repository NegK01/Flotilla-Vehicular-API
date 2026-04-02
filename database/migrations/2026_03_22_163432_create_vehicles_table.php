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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('plate', 20);
            $table->string('brand', 100);
            $table->string('model', 100);
            $table->unsignedSmallInteger('year');
            $table->string('vehicle_type', 50);
            $table->unsignedTinyInteger('capacity');
            $table->string('fuel_type', 50);
            $table->string('image_path', 255);
            $table->enum('status', ['available', 'reserved', 'maintenance', 'out_of_service'])->default('available');
            $table->unsignedInteger('current_mileage')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("
            CREATE UNIQUE INDEX vehicles_plate_active_unique
            ON vehicles (plate)
            WHERE deleted_at IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS vehicles_plate_active_unique");
        Schema::dropIfExists('vehicles');
    }
};

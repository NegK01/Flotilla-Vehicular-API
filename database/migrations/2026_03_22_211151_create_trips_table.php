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
        // FK[vehicle_request_id] = el enunciado de viajes dice que la salida/devolución debe quedar asociada a una asignación o solicitud aprobada. Entonces esta FK permite saber de qué solicitud aprobada nació el viaje
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_request_id')->nullable()->constrained('vehicle_requests');
            $table->foreignId('driver_id')->constrained('users');
            $table->foreignId('vehicle_id')->constrained('vehicles');
            $table->foreignId('travel_route_id')->nullable()->constrained('travel_routes');
            $table->dateTime('departure_at');
            $table->dateTime('return_at')->nullable();
            $table->unsignedInteger('departure_mileage');
            $table->unsignedInteger('return_mileage')->nullable();
            $table->text('observations')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};

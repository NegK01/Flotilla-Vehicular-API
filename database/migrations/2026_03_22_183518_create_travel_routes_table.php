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
        Schema::create('travel_routes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('start_point', 150);
            $table->string('end_point', 150);
            $table->decimal('estimated_distance', 8, 2)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_routes');
    }
};

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
        Schema::create('services_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('services_id')->constrained('services')->onDelete('cascade');
            $table->json('time_block_ids');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('services_availabilities');

    }
};

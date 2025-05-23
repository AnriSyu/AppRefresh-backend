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
        //
        Schema::create('time_blocks', function (Blueprint $table) {
            $table->id();
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']);
            $table->string('hours');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('time_blocks');
    }
};

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
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->foreignId('badge_id')->nullable()->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('level')->default(0);
            $table->decimal('diamonds', 10, 2)->default(0);
            $table->decimal('coins_spent', 10, 2)->default(0);
            $table->decimal('coins_earned', 10, 2)->default(0);
            $table->unsignedBigInteger('no_of_livestreams')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rewards');
    }
};

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
        Schema::create('livestreams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('moderator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('parent_livestream_id')->nullable()->constrained('livestreams')->cascadeOnDelete();
            $table->boolean('is_creator')->default(true);
            $table->boolean('is_live')->default(true);
            $table->string('title');
            $table->string('description');
            $table->string('country');
            $table->string('type'); //single, multi, pk
            $table->string('block_list')->nullable();
            $table->unsignedBigInteger('no_of_views')->default(0);
            $table->unsignedBigInteger('no_of_likes')->default(0);
            $table->decimal('coins_earned', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('livestreams');
    }
};

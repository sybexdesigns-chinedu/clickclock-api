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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->string('username')->unique();
            $table->string('name');
            $table->text('bio')->nullable();
            $table->string('gender');
            $table->date('dob');
            $table->string('image');
            $table->string('phone');
            $table->string('city');
            $table->string('country');
            $table->string('social_link')->nullable();
            $table->string('blocked_users')->nullable();
            $table->boolean('is_private')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};

<?php

use App\Models\User;
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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->string('file_url');
            $table->text('caption')->nullable();
            $table->string('hashtags')->nullable()->index();
            $table->enum('privacy', ['public', 'friends', 'private'])->default('public');
            $table->enum('status', ['approved', 'flagged', 'rejected'])->default('approved');
            $table->unsignedBigInteger('no_of_engagements')->default(0)->index();
            $table->string('location')->nullable();
            $table->string('remark')->nullable();
            $table->string('meta_location');
            $table->boolean('allow_comments');
            $table->boolean('has_video')->default(false);
            $table->boolean('allow_duet')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};

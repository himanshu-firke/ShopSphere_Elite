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
        Schema::create('review_moderation_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('moderator_id')->constrained('users')->onDelete('cascade');
            $table->string('action');
            $table->string('reason')->nullable();
            $table->timestamps();

            // Indexes for faster lookups
            $table->index(['product_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['moderator_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_moderation_log');
    }
}; 
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
        Schema::create('prestations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('excerpt', 500);
            $table->longText('content')->nullable();
            $table->string('icon', 100)->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_published', 'sort_order']);
            $table->index('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestations');
    }
};

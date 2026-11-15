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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('company', 150)->nullable();
            $table->string('email');
            $table->string('phone', 30)->nullable();
            $table->string('project_type', 50);
            $table->string('budget', 50)->nullable();
            $table->text('message');
            $table->timestamp('privacy_accepted_at');
            $table->string('status', 30)->default('new');
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('email');
            $table->index('last_name');
            $table->index('company');
            $table->index('created_at');
            $table->index('archived_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};

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
        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description');
            $table->string('venue');
            $table->timestampTz('starts_at')->index();
            $table->timestampTz('ends_at');
            $table->unsignedInteger('capacity');
            $table->unsignedInteger('reserved_count')->default(0);
            $table->string('status')->default('DRAFT')->index();
            $table->foreignUuid('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['status', 'starts_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};

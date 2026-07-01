<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('child_reinscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('children')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('period_id')->constrained('periods')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('period_movement_id')->constrained('period_movements')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('from_level_ids');
            $table->json('to_level_ids');
            $table->string('notes', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['child_id', 'period_id']);
            $table->index('period_movement_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('child_reinscriptions');
    }
};

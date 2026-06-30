<?php

use App\Globals\Status;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('child_level_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('children')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('level_id')->constrained('levels')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('period_id')->constrained('periods')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('period_movement_id')->constrained('period_movements')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', [
                Status::ACTIVE,
                Status::INACTIVE,
                Status::COMPLETED,
                Status::WITHDRAW,
            ])->default(Status::ACTIVE);
            $table->date('assigned_at');
            $table->date('ended_at')->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['child_id', 'status']);
            $table->index(['level_id', 'status']);
            $table->index(['period_id', 'status']);
            $table->index('period_movement_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('child_level_assignments');
    }
};

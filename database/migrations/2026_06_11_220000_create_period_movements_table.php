<?php

use App\Globals\Status;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('period_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('periods');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('period_movement_type_id')->nullable()->constrained('period_movement_types')->nullOnDelete();
            $table->enum('status', [
                Status::PENDING,
                Status::IN_PROGRESS,
                Status::COMPLETED,
            ])->default(Status::PENDING);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('notes', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('start_date');
            $table->index('end_date');
            $table->index('period_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('period_movements');
    }
};

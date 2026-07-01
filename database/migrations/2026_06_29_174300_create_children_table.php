<?php

use App\Globals\BloodType;
use App\Globals\Sex;
use App\Globals\Status;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('children', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('church_id')->constrained('churches')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('community_id')->constrained('communities')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('name', 150);
            $table->string('paterno', 150);
            $table->string('materno', 150)->nullable();
            $table->string('code', 40)->unique();
            $table->date('birthdate');
            $table->enum('sex', Sex::values());
            $table->string('email', 255)->nullable();
            $table->string('phone_lada', 8)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('emergency_phone_lada', 8)->nullable();
            $table->string('emergency_phone', 30)->nullable();
            $table->enum('blood_type', BloodType::values())->default(BloodType::UNKNOWN);
            $table->text('observations')->nullable();
            $table->boolean('privacy_terms')->default(false);
            $table->enum('status', [
                Status::ACTIVE,
                Status::INACTIVE,
                Status::COMPLETED,
                Status::WITHDRAW,
                Status::SUSPENDED,
            ])->default(Status::ACTIVE);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['church_id', 'status']);
            $table->index(['community_id', 'status']);
            $table->index(['paterno', 'materno', 'name']);
            $table->index('birthdate');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('children');
    }
};

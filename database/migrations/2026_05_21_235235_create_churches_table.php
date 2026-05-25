<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Globals\Status;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('churches', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('alias', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('address', 255)->nullable();

            $table->foreignId('municipality_id')->nullable()->constrained('municipalities')->nullOnDelete();

            $table->foreignId('deanery_id')->nullable()->constrained('deaneries')->nullOnDelete();

            $table->enum('status', [Status::ACTIVE, Status::INACTIVE])->default(Status::ACTIVE);

            $table->timestamps();
            $table->softDeletes();

            // Indexes útiles
            $table->index('name');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('churches');
    }
};

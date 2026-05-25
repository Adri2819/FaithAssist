<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Globals\Status;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chapels', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('address', 255)->nullable();

            $table->foreignId('community_id')->nullable()->constrained('communities')->nullOnDelete();

            $table->foreignId('church_id')->nullable()->constrained('churches')->nullOnDelete();

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
        Schema::dropIfExists('chapels');
    }
};

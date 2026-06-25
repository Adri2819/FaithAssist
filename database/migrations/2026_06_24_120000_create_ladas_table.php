<?php

use App\Globals\Status;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ladas')) {
            return;
        }

        Schema::create('ladas', function (Blueprint $table) {
            $table->id();
            $table->string('country', 80);
            $table->string('code', 8)->unique();
            $table->enum('status', [Status::ACTIVE, Status::INACTIVE])->default(Status::ACTIVE);
            $table->timestamps();

            $table->index('country');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ladas');
    }
};

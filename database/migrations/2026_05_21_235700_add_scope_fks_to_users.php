<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('diocese_id')
                ->nullable()
                ->after('id')
                ->constrained('dioceses')
                ->nullOnDelete();

            $table->foreignId('deanery_id')
                ->nullable()
                ->after('diocese_id')
                ->constrained('deaneries')
                ->nullOnDelete();

            $table->foreignId('church_id')
                ->nullable()
                ->after('deanery_id')
                ->constrained('churches')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['church_id']);
            $table->dropForeign(['deanery_id']);
            $table->dropForeign(['diocese_id']);
            $table->dropColumn(['diocese_id', 'deanery_id', 'church_id']);
        });
    }
};

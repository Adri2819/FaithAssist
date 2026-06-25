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

        Schema::dropIfExists('scopes');
    }

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropForeign(['church_id']);
        $table->dropForeign(['deanery_id']);
        $table->dropForeign(['diocese_id']);
        $table->dropColumn(['diocese_id', 'deanery_id', 'church_id']);
    });

    Schema::create('scopes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->string('scope_type', 50);
        $table->unsignedBigInteger('scope_id');
        $table->timestamps();

        $table->unique(['user_id', 'scope_type', 'scope_id']);
        $table->index(['user_id', 'scope_type']);
        $table->index(['scope_type', 'scope_id']);
    });
}
};

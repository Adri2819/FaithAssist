<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('municipality_id')
                ->nullable()
                ->after('id')
                ->constrained('municipalities')
                ->nullOnDelete();

            $table->foreignId('church_id')
                ->nullable()
                ->after('municipality_id')
                ->constrained('churches')
                ->nullOnDelete();
        });

        Schema::dropIfExists('scopes');
    }

    public function down(): void
    {
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

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['church_id']);
            $table->dropForeign(['municipality_id']);
            $table->dropColumn(['municipality_id', 'church_id']);
        });
    }
};

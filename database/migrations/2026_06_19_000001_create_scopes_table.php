<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TYPE_MUNICIPALITY = 'municipality';

    private const TYPE_CHURCH = 'church';

    private const TYPE_COMMUNITY = 'community';

    public function up(): void
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

        $this->copyPivotToScopes('municipality_user', 'municipality_id', self::TYPE_MUNICIPALITY);
        $this->copyPivotToScopes('church_user', 'church_id', self::TYPE_CHURCH);
        $this->copyPivotToScopes('community_user', 'community_id', self::TYPE_COMMUNITY);

        Schema::dropIfExists('municipality_user');
        Schema::dropIfExists('church_user');
        Schema::dropIfExists('community_user');
    }

    public function down(): void
    {
        Schema::create('community_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('community_id')->constrained('communities')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'community_id']);
        });

        Schema::create('church_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('church_id')->constrained('churches')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'church_id']);
        });

        Schema::create('municipality_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('municipality_id')->constrained('municipalities')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'municipality_id']);
        });

        $this->copyScopesToPivot(self::TYPE_COMMUNITY, 'community_user', 'community_id');
        $this->copyScopesToPivot(self::TYPE_CHURCH, 'church_user', 'church_id');
        $this->copyScopesToPivot(self::TYPE_MUNICIPALITY, 'municipality_user', 'municipality_id');

        Schema::dropIfExists('scopes');
    }

    private function copyPivotToScopes(string $table, string $scopeColumn, string $scopeType): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        DB::table($table)
            ->orderBy('id')
            ->chunk(500, function ($rows) use ($scopeColumn, $scopeType): void {
                $records = $rows
                    ->map(fn (object $row): array => [
                        'user_id' => (int) $row->user_id,
                        'scope_type' => $scopeType,
                        'scope_id' => (int) $row->{$scopeColumn},
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                    ])
                    ->all();

                DB::table('scopes')->insertOrIgnore($records);
            });
    }

    private function copyScopesToPivot(string $scopeType, string $table, string $scopeColumn): void
    {
        if (! Schema::hasTable('scopes')) {
            return;
        }

        DB::table('scopes')
            ->where('scope_type', $scopeType)
            ->orderBy('id')
            ->chunk(500, function ($rows) use ($table, $scopeColumn): void {
                $records = $rows
                    ->map(fn (object $row): array => [
                        'user_id' => (int) $row->user_id,
                        $scopeColumn => (int) $row->scope_id,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                    ])
                    ->all();

                DB::table($table)->insertOrIgnore($records);
            });
    }
};
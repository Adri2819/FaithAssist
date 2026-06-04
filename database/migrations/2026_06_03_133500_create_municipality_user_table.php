<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('municipality_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('municipality_id')->constrained('municipalities')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'municipality_id']);
        });

        if (Schema::hasTable('community_user')) {
            $assignments = DB::table('community_user')
                ->join('communities', 'communities.id', '=', 'community_user.community_id')
                ->whereNotNull('communities.municipality_id')
                ->select([
                    'community_user.user_id',
                    'communities.municipality_id',
                    'community_user.created_at',
                    'community_user.updated_at',
                ])
                ->get()
                ->map(fn ($row): array => [
                    'user_id' => (int) $row->user_id,
                    'municipality_id' => (int) $row->municipality_id,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ])
                ->unique(fn (array $row): string => "{$row['user_id']}:{$row['municipality_id']}")
                ->values()
                ->all();

            if ($assignments !== []) {
                DB::table('municipality_user')->insertOrIgnore($assignments);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('municipality_user');
    }
};

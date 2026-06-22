<?php

use App\Globals\Status;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('period_movements', function (Blueprint $table) {
            $table->foreignId('period_movement_type_id')
                ->nullable()
                ->after('period_id')
                ->constrained('period_movement_types');
        });

        Schema::table('period_movements', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropColumn('type');
        });
    }

    public function down(): void
    {
        Schema::table('period_movements', function (Blueprint $table) {
            $table->string('type', 50)->nullable()->after('period_id');
            $table->index('type');
        });
        
        Schema::table('period_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('period_movement_type_id');
        });
    }
};

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
        if (! Schema::hasTable('ladas')) {
            return;
        }

        Schema::table('ladas', function (Blueprint $table) {
            if (! Schema::hasColumn('ladas', 'country')) {
                $table->string('country', 80)->nullable()->after('code');
            }

            if (! Schema::hasColumn('ladas', 'label')) {
                $table->string('label', 120)->nullable()->after('country');
            }

            if (! Schema::hasColumn('ladas', 'status')) {
                $table->string('status', 20)->default(Status::ACTIVE)->after('label');
                $table->index('status');
            }
        });

        if (Schema::hasColumn('ladas', 'country') && Schema::hasColumn('ladas', 'label')) {
            DB::table('ladas')
                ->whereNull('country')
                ->whereNotNull('label')
                ->update(['country' => DB::raw('label')]);

            DB::table('ladas')
                ->whereNull('label')
                ->whereNotNull('country')
                ->update(['label' => DB::raw('country')]);
        }

        if (Schema::hasColumn('ladas', 'status')) {
            DB::table('ladas')
                ->whereNull('status')
                ->update(['status' => Status::ACTIVE]);
        }
    }

    public function down(): void
    {
        // Mantener compatibilidad hacia atrás: no se eliminan columnas preexistentes.
    }
};

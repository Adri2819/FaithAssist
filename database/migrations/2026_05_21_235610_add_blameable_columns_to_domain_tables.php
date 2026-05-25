<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'states',
            'municipalities',
            'communities',
            'dioceses',
            'deaneries',
            'churches',
            'chapels',
            'modules',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->after('id')->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
                $table->foreignId('deleted_by')->nullable()->after('updated_by')->constrained('users')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'states',
            'municipalities',
            'communities',
            'dioceses',
            'deaneries',
            'churches',
            'chapels',
            'modules',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('deleted_by');
                $table->dropConstrainedForeignId('updated_by');
                $table->dropConstrainedForeignId('created_by');
            });
        }
    }
};

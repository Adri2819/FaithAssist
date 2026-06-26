<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('whatsapp_messages', 'country_code')) {
            return;
        }

        Schema::table('whatsapp_messages', function (Blueprint $table) {
            $table->string('country_code', 8)->nullable()->after('to_phone');
            $table->index('country_code');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            $table->dropIndex(['country_code']);
            $table->dropColumn('country_code');
        });
    }
};

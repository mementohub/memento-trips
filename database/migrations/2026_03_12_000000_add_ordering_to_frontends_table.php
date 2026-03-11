<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('frontends', function (Blueprint $table) {
            $table->integer('ordering')->default(0)->after('data_translations');
        });
    }

    public function down(): void
    {
        Schema::table('frontends', function (Blueprint $table) {
            $table->dropColumn('ordering');
        });
    }
};

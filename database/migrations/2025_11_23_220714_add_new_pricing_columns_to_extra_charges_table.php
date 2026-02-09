<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('extra_charges', function (Blueprint $table) {
            $table->decimal('general_price', 10, 2)->nullable()->after('price_type');
            $table->decimal('adult_price', 10, 2)->nullable()->after('general_price');
            $table->decimal('child_price', 10, 2)->nullable()->after('adult_price');
            $table->decimal('infant_price', 10, 2)->nullable()->after('child_price');
        });
    }

    public function down(): void
    {
        Schema::table('extra_charges', function (Blueprint $table) {
            $table->dropColumn(['general_price', 'adult_price', 'child_price', 'infant_price']);
        });
    }
};
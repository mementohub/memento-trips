<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Make address/coords optional + add age_category_prices
        Schema::table('pickup_points', function (Blueprint $table) {
            $table->string('address')->nullable()->change();
            $table->decimal('latitude', 10, 8)->nullable()->change();
            $table->decimal('longitude', 11, 8)->nullable()->change();
            $table->json('age_category_prices')->nullable()->after('charge_type')
                  ->comment('Per-person age category prices: {adult: X, child: Y, baby: Z, infant: W}');
        });

        // Migrate existing data: flat → per_booking, per_person/per_adult/per_child → per_person
        DB::table('pickup_points')
            ->where('charge_type', 'flat')
            ->update(['charge_type' => 'per_booking']);

        DB::table('pickup_points')
            ->whereIn('charge_type', ['per_person', 'per_adult', 'per_child'])
            ->update(['charge_type' => 'per_person']);
    }

    public function down(): void
    {
        // Revert data
        DB::table('pickup_points')
            ->where('charge_type', 'per_booking')
            ->update(['charge_type' => 'flat']);

        Schema::table('pickup_points', function (Blueprint $table) {
            $table->dropColumn('age_category_prices');
        });
    }
};

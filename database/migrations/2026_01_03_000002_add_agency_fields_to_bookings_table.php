<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'agency_user_id')) {
                $table->unsignedBigInteger('agency_user_id')->nullable()->index()->after('user_id');
            }

            if (!Schema::hasColumn('bookings', 'agency_client_id')) {
                $table->unsignedBigInteger('agency_client_id')->nullable()->index()->after('agency_user_id');
            }

            if (!Schema::hasColumn('bookings', 'commission_amount')) {
                $table->decimal('commission_amount', 12, 2)->nullable()->after('total');
            }

            // FK-uri (safe, păstrăm istoricul dacă se șterge agency/client)
            $table->foreign('agency_user_id')
                ->references('id')->on('users')
                ->nullOnDelete();

            $table->foreign('agency_client_id')
                ->references('id')->on('agency_clients')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // drop FK dacă există
            try { $table->dropForeign(['agency_user_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['agency_client_id']); } catch (\Throwable $e) {}

            if (Schema::hasColumn('bookings', 'agency_client_id')) {
                $table->dropColumn('agency_client_id');
            }
            if (Schema::hasColumn('bookings', 'agency_user_id')) {
                $table->dropColumn('agency_user_id');
            }
            if (Schema::hasColumn('bookings', 'commission_amount')) {
                $table->dropColumn('commission_amount');
            }
        });
    }
};
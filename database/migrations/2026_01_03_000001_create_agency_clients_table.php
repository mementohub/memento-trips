<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('agency_clients', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('agency_user_id')->index();

            $table->string('first_name', 120);
            $table->string('last_name', 120);
            $table->string('email', 190)->nullable()->index();
            $table->string('phone', 50)->nullable();

            $table->string('country', 120)->nullable();
            $table->string('state', 120)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('address', 255)->nullable();

            $table->text('notes')->nullable();

            // GDPR (minim)
            $table->string('lawful_basis', 50)->nullable(); // ex: contract/consent/legitimate_interest
            $table->timestamp('consent_email_marketing_at')->nullable();
            $table->string('privacy_notice_version', 50)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('agency_user_id')
                ->references('id')->on('users')
                ->onDelete('cascade'); // dacă se șterge agency-ul, dispar și clienții
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agency_clients');
    }
};
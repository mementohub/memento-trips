<?php

namespace Modules\PaymentGateway\Database\Seeders;

use Illuminate\Database\Seeder;

class PaymentGatewayDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
// ---------------------------
// PAYU DEFAULT CONFIGURATION
// ---------------------------
$payu_keys = [
    'payu_status' => 0,
    'payu_currency_id' => 'RON',
    'payu_merchant_pos_id' => '491057',
    'payu_secret_key' => 'bac30a8686d9a31cbc9f3a25553e2f62',
    'payu_client_id' => '491057',
    'payu_client_secret' => 'd807005b11ca38eea8a3c1a794635b4f',
    'payu_sandbox' => 1,
    'payu_image' => 'uploads/default/payu.png',
];

// creează sau actualizează fiecare cheie
foreach ($payu_keys as $key => $value) {
    \Modules\PaymentGateway\App\Models\PaymentGateway::updateOrCreate(
        ['key' => $key],
        ['value' => $value]
    );
}

    }
}

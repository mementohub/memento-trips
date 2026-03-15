<?php

declare(strict_types=1);

namespace Modules\TourBooking\App\Http\Controllers\Front;

use Exception;
use Stripe\Charge;
use Stripe\Stripe;
use Razorpay\Api\Api;
use Illuminate\Http\Request;
use Mollie\Laravel\Facades\Mollie;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Schema;
use Modules\Currency\App\Models\Currency;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Modules\PaymentGateway\App\Models\PaymentGateway;
use Modules\Coupon\App\Http\Controllers\CouponController;
use Modules\TourBooking\App\Models\Booking;
use Modules\TourBooking\App\Models\Service;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * PaymentController
 *
 * Processes payment transactions for tour bookings across multiple gateways (Stripe, PayPal, PayU Romania, Razorpay, Flutterwave, Paystack, Mollie, Instamojo, Bank Transfer). Creates booking orders with commission calculations and agency context support.
 *
 * @package Modules\TourBooking\App\Http\Controllers\Front
 */
class PaymentController extends Controller
{
    public $payment_setting;

    public function __construct()
    {
        $payment_data = PaymentGateway::all();

        // ✅ fix: ensure array exists (prevents undefined var issues)
        $payment_setting = [];

        foreach ($payment_data as $data_item) {
            $payment_setting[$data_item->key] = $data_item->value;
        }

        $this->payment_setting = (object) $payment_setting;
    }
    
    private function isActiveAgencyUser($user): bool
{
    if (!$user) return false;

    $isAgencyEnabled = (int) ($user->is_seller ?? 0) === 1;
    $isApproved = ($user->instructor_joining_request ?? null) === 'approved';

    return $isAgencyEnabled && $isApproved;
}

/**
 * Capturează contextul de agency direct din request-ul de plată (checkout),
 * fără wizard. Contextul ajunge în session și e consumat în create_order().
 *
 * Așteaptă în request:
 * - book_as_agency = 1/0
 * - agency_client_id (opțional)
 * - sau câmpuri pentru client nou:
 *   agency_new_first_name, agency_new_last_name, agency_new_email, agency_new_phone, agency_new_address, etc.
 */
private function captureAgencyContextFromRequest(Request $request): void
{
    $auth = Auth::guard('web')->user();

    // dacă nu e agency activ/approved, curățăm orice context
    if (!$this->isActiveAgencyUser($auth)) {
        Session::forget('agency_booking_context');
        return;
    }

    // dacă nu e bifat "Book as agency", curățăm contextul
    if (!$request->boolean('book_as_agency')) {
        Session::forget('agency_booking_context');
        return;
    }

    $clientId = (int) $request->input('agency_client_id', 0);

    // Dacă nu avem clientId, dar avem date de client nou -> îl creăm în DB
    $newFirst = trim((string) $request->input('agency_new_first_name', ''));
    $newLast  = trim((string) $request->input('agency_new_last_name', ''));

    if ($clientId <= 0 && ($newFirst !== '' || $newLast !== '')) {
        // minim: first + last
        if ($newFirst === '' || $newLast === '') {
            // Nu aruncăm excepție ca să nu stricăm gateway-ul,
            // dar nici nu activăm agency booking fără client valid.
            Session::forget('agency_booking_context');
            return;
        }

        $clientId = (int) DB::table('agency_clients')->insertGetId([
            'agency_user_id' => (int) $auth->id,
            'first_name'     => $newFirst,
            'last_name'      => $newLast,
            'email'          => $request->input('agency_new_email') ?: null,
            'phone'          => $request->input('agency_new_phone') ?: null,
            'country'        => $request->input('agency_new_country') ?: null,
            'state'          => $request->input('agency_new_state') ?: null,
            'city'           => $request->input('agency_new_city') ?: null,
            'address'        => $request->input('agency_new_address') ?: null,
            'notes'          => $request->input('agency_new_notes') ?: null,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }

    if ($clientId <= 0) {
        Session::forget('agency_booking_context');
        return;
    }

    // Security: clientul trebuie să aparțină agency-ului curent
    $client = DB::table('agency_clients')
        ->where('id', $clientId)
        ->where('agency_user_id', (int) $auth->id)
        ->whereNull('deleted_at')
        ->first();

    if (!$client) {
        Session::forget('agency_booking_context');
        return;
    }

    // salvăm contextul în sesiune (consumat în create_order)
    Session::put('agency_booking_context', [
        'agency_user_id'   => (int) $auth->id,
        'agency_client_id' => (int) $client->id,

        // override customer fields (opțional, dar util)
        'customer_name'    => trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? '')),
        'customer_email'   => $client->email ?? null,
        'customer_phone'   => $client->phone ?? null,
        'customer_address' => $client->address ?? null,
    ]);
}

    /* =======================
     * GATEWAYS
     * ======================= */

    public function stripe_payment(Request $request)
    {
        $auth_user = Auth::guard('web')->user();
        $this->setAgencyBookingContextFromRequest($request, $auth_user);
        $calculate_price = $this->calculate_price();
        $stripe_currency = Currency::findOrFail($this->payment_setting->stripe_currency_id);

        // curs aplicat corect
        $payable_amount = round((($calculate_price['total_amount'] ?? 0) * ($stripe_currency->currency_rate ?? 1)), 2);

        Stripe::setApiKey($this->payment_setting->stripe_secret);
        $customerInfo = $this->customerInfo($request);

        try {
            $result = Charge::create([
                "amount"      => (int) round($payable_amount * 100),
                "currency"    => $stripe_currency->currency_code ?? 'USD',
                "source"      => $request->stripeToken,
                "description" => config('app.name'),
            ]);
        } catch (Exception $ex) {
            Log::info('Stripe payment : ' . $ex->getMessage());
            return redirect()->back()->with([
                'message' => trans('translate.Something went wrong, please try again') . ' ' . $ex->getMessage(),
                'alert-type' => 'error'
            ]);
        }

        $order = $this->create_order($auth_user, 'Stripe', 'success', $result->balance_transaction, $customerInfo);

        return $this->successRedirect($order);
    }

    /* =======================
     * PAYU ROMANIA – REBUILT
     * =======================
     *
     * Flow:
     *   1. Checkout JS sends AJAX POST → payu_payment()
     *   2. Controller gets OAuth2 token → creates order at PayU REST API
     *   3. Returns JSON { redirect_url } to the browser
     *   4. User completes payment on PayU page
     *   5. PayU redirects to continueUrl → payu_callback()
     *   6. Callback verifies order status via API and creates internal booking
     *
     * Fixed: AJAX JSON response, currency from DB, orderId tracking,
     *        consistent OAuth, sandbox/production toggle.
     */

    /**
     * Get the PayU API base URL based on sandbox setting.
     */
    private function payuApiBase(): string
    {
        $sandbox = (int) ($this->payment_setting->payu_sandbox ?? 1);
        return $sandbox
            ? 'https://secure.snd.payu.com'   // sandbox
            : 'https://secure.payu.com';       // production
    }

    /**
     * Obtain an OAuth2 Bearer token from PayU.
     */
    private function payuGetToken(): ?string
    {
        $apiBase   = $this->payuApiBase();
        $clientId  = $this->payment_setting->payu_client_id     ?? '';
        $secret    = $this->payment_setting->payu_client_secret ?? '';

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => "{$apiBase}/pl/standard/user/oauth/authorize",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'grant_type'    => 'client_credentials',
                'client_id'     => $clientId,
                'client_secret' => $secret,
            ]),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT        => 15,
        ]);
        $raw = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            Log::error('PayU OAuth cURL error', ['error' => $err]);
            return null;
        }

        $data = json_decode($raw, true);
        Log::info('PayU OAuth response', ['response' => $data]);

        return $data['access_token'] ?? null;
    }

    /**
     * Step 1 – AJAX POST from checkout.
     * Creates a PayU order and returns JSON { redirect_url }.
     */
    public function payu_payment(Request $request)
    {
        Log::info('=== PAYU PAYMENT START ===', $request->all());

        try {
            // ── context ─────────────────────────────────────
            $auth_user   = Auth::guard('web')->user();
            $this->setAgencyBookingContextFromRequest($request, $auth_user);

            $customerInfo    = $this->customerInfo($request);
            $calculate_price = $this->calculate_price();
            $totalAmount     = (float) ($calculate_price['total_amount'] ?? 0);

            // ── currency conversion ─────────────────────────
            $payu_currency = Currency::find($this->payment_setting->payu_currency_id ?? null);
            $currencyCode  = $payu_currency->currency_code ?? 'PLN';
            $currencyRate  = (float) ($payu_currency->currency_rate ?? 1);

            $payableAmount = round($totalAmount * $currencyRate, 2);
            // PayU expects amount in smallest unit (grosze / bani)
            $amountInCents = (int) round($payableAmount * 100);

            // ── PayU credentials ────────────────────────────
            $posId   = $this->payment_setting->payu_merchant_pos_id ?? '';
            $apiBase = $this->payuApiBase();

            $extOrderId  = 'MEM_' . time() . '_' . mt_rand(1000, 9999);
            $continueUrl = route('payment.payu-callback');
            $description = config('app.name') . ' – Booking ' . $extOrderId;

            // ── get OAuth2 token ────────────────────────────
            $token = $this->payuGetToken();
            if (!$token) {
                return response()->json([
                    'error' => 'Nu s-a putut obține tokenul PayU. Verifică credențialele.'
                ], 500);
            }

            // ── build order payload ─────────────────────────
            $payload = [
                'notifyUrl'     => $continueUrl,
                'continueUrl'   => $continueUrl,
                'customerIp'    => $request->ip() ?: '127.0.0.1',
                'merchantPosId' => $posId,
                'description'   => $description,
                'currencyCode'  => $currencyCode,
                'totalAmount'   => $amountInCents,
                'extOrderId'    => $extOrderId,
                'buyer'         => [
                    'email'     => $customerInfo['customer_email'] ?? 'guest@example.com',
                    'phone'     => $customerInfo['customer_phone'] ?: '0000000000',
                    'firstName' => $customerInfo['customer_name']  ?: 'Client',
                    'language'  => 'ro',
                ],
                'products'      => [[
                    'name'      => 'Tour Booking',
                    'unitPrice' => $amountInCents,
                    'quantity'  => 1,
                ]],
            ];

            Log::info('PayU order payload', $payload);

            // ── create order at PayU ────────────────────────
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => "{$apiBase}/api/v2_1/orders",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode($payload),
                CURLOPT_FOLLOWLOCATION => false,   // PayU returns 302 – we need the Location header
                CURLOPT_HEADER         => true,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    "Authorization: Bearer {$token}",
                ],
                CURLOPT_TIMEOUT        => 30,
            ]);
            $rawResponse = curl_exec($ch);
            $httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr     = curl_error($ch);
            curl_close($ch);

            if ($curlErr) {
                Log::error('PayU order cURL error', ['error' => $curlErr]);
                return response()->json(['error' => 'Eroare comunicare PayU.'], 500);
            }

            // ── parse response ──────────────────────────────
            // PayU can return 302 + Location header OR 200 with JSON body
            $headerSize = strpos($rawResponse, "\r\n\r\n");
            $headers    = substr($rawResponse, 0, $headerSize);
            $body       = substr($rawResponse, $headerSize + 4);
            $result     = json_decode($body, true);

            Log::info('PayU order response', [
                'httpCode' => $httpCode,
                'body'     => $result,
            ]);

            // Extract redirect URL: prefer redirectUri in body, fall back to Location header
            $redirectUrl = $result['redirectUri'] ?? null;
            if (!$redirectUrl && preg_match('/^Location:\s*(.+)$/mi', $headers, $m)) {
                $redirectUrl = trim($m[1]);
            }

            // Get the PayU orderId (different from our extOrderId)
            $payuOrderId = $result['orderId'] ?? $extOrderId;

            $statusCode = $result['status']['statusCode'] ?? '';
            if ($redirectUrl && in_array($statusCode, ['SUCCESS', 'WARNING_CONTINUE_REDIRECT'], true)) {
                // Store order context in session for callback
                Session::put('payu_order', [
                    'ext_order_id'  => $extOrderId,
                    'payu_order_id' => $payuOrderId,
                    'amount'        => $payableAmount,
                    'method'        => 'PayU',
                    'customer'      => $customerInfo,
                ]);

                Log::info('PayU redirect URL obtained', ['url' => $redirectUrl]);

                // AJAX handler expects JSON with redirect_url
                return response()->json(['redirect_url' => $redirectUrl]);
            }

            // Error path
            $errorMsg = $result['status']['statusDesc']
                     ?? $result['status']['codeLiteral']
                     ?? 'Răspuns neașteptat de la PayU.';
            Log::error('PayU order creation failed', ['result' => $result]);
            return response()->json(['error' => "Eroare PayU: {$errorMsg}"], 422);

        } catch (\Exception $e) {
            Log::error('PayU exception', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Eroare PayU: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Step 2 – PayU redirects user here after payment.
     * Verifies the order status and creates the internal booking.
     */
    public function payu_callback(Request $request)
    {
        Log::info('=== PAYU CALLBACK ===', $request->all());

        $payuOrder = Session::get('payu_order');
        if (!$payuOrder) {
            return redirect()->route('user.bookings.index')->with([
                'message'    => 'Datele comenzii PayU lipsesc din sesiune.',
                'alert-type' => 'error',
            ]);
        }

        $payuOrderId = $payuOrder['payu_order_id'];
        $customer    = $payuOrder['customer'];
        $auth_user   = Auth::guard('web')->user();
        $apiBase     = $this->payuApiBase();

        // ── get fresh token ─────────────────────────────
        $token = $this->payuGetToken();
        if (!$token) {
            return redirect()->route('user.bookings.index')->with([
                'message'    => 'Eroare PayU: nu s-a putut obține tokenul pentru verificare.',
                'alert-type' => 'error',
            ]);
        }

        // ── verify order status ─────────────────────────
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => "{$apiBase}/api/v2_1/orders/{$payuOrderId}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPGET        => true,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer {$token}",
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT        => 15,
        ]);
        $raw = curl_exec($ch);
        curl_close($ch);

        $verifyData = json_decode($raw, true);
        Log::info('PayU verify response', ['data' => $verifyData]);

        $orderStatus = $verifyData['orders'][0]['status'] ?? 'UNKNOWN';
        $transactionId = $verifyData['orders'][0]['orderId'] ?? $payuOrderId;

        if (in_array($orderStatus, ['COMPLETED', 'WAITING_FOR_CONFIRMATION'], true)) {
            $order = $this->create_order($auth_user, 'PayU', 'success', $transactionId, $customer);
            Session::forget('payu_order');

            return $this->successRedirect($order);
        }

        // Payment not completed – could be PENDING, CANCELED, etc.
        Session::forget('payu_order');
        Log::warning('PayU payment not completed', ['status' => $orderStatus]);

        return redirect()->route('user.bookings.index')->with([
            'message'    => "Plata PayU nu a fost finalizată. Status: {$orderStatus}",
            'alert-type' => 'error',
        ]);
    }

    public function paypal_payment(Request $request)
    {
        $this->setCustomerInfoSession($request);

        $calculate_price = $this->calculate_price();
        $paypal_currency = Currency::findOrFail($this->payment_setting->paypal_currency_id);
        $payable_amount  = round((($calculate_price['total_amount'] ?? 0) * ($paypal_currency->currency_rate ?? 1)), 2);

        config(['paypal.mode' => $this->payment_setting->paypal_account_mode]);

        if ($this->payment_setting->paypal_account_mode == 'sandbox') {
            config(['paypal.sandbox.client_id' => $this->payment_setting->paypal_client_id]);
            config(['paypal.sandbox.client_secret' => $this->payment_setting->paypal_secret_key]);
        } else {
            config(['paypal.live.client_id' => $this->payment_setting->paypal_client_id]);
            config(['paypal.live.client_secret' => $this->payment_setting->paypal_secret_key]);
            config(['paypal.live.app_id' => 'APP-80W284485P519543T']);
        }

        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();
        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => route('payment.paypal-success-payment'),
                "cancel_url" => route('payment.paypal-faild-payment'),
            ],
            "purchase_units" => [[
                "amount" => [
                    "currency_code" => $paypal_currency->currency_code,
                    "value" => $payable_amount
                ]
            ]]
        ]);

        if (isset($response['id']) && $response['id'] != null) {
            foreach ($response['links'] as $links) {
                if ($links['rel'] == 'approve') {
                    return redirect()->away($links['href']);
                }
            }
        }

        return redirect()->back()->with([
            'message' => trans('translate.Something went wrong, please try again'),
            'alert-type' => 'error'
        ]);
    }

    public function paypal_success_payment(Request $request)
    {
        $customerInfo = Session::get('customer_info');

        config(['paypal.mode' => $this->payment_setting->paypal_account_mode]);
        if ($this->payment_setting->paypal_account_mode == 'sandbox') {
            config(['paypal.sandbox.client_id' => $this->payment_setting->paypal_client_id]);
            config(['paypal.sandbox.client_secret' => $this->payment_setting->paypal_secret_key]);
        } else {
            config(['paypal.live.client_id' => $this->payment_setting->paypal_client_id]);
            config(['paypal.live.client_secret' => $this->payment_setting->paypal_secret_key]);
            config(['paypal.live.app_id' => 'APP-80W284485P519543T']);
        }

        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();
        $response = $provider->capturePaymentOrder($request['token']);

        if (isset($response['status']) && $response['status'] == 'COMPLETED') {
            $auth_user = Auth::guard('web')->user();
            $order = $this->create_order($auth_user, 'Paypal', 'success', $request->PayerID, $customerInfo);

            return $this->successRedirect($order);
        }

        return redirect()->back()->with([
            'message' => trans('translate.Something went wrong, please try again'),
            'alert-type' => 'error'
        ]);
    }

    public function paypal_faild_payment(Request $request)
    {
        return redirect()->back()->with([
            'message' => trans('translate.Something went wrong, please try again'),
            'alert-type' => 'error'
        ]);
    }

    public function razorpay_payment(Request $request)
    {
        $input = $request->all();
        $customerInfo = $this->customerInfo($request);

        $api = new Api($this->payment_setting->razorpay_key, $this->payment_setting->razorpay_secret);
        $payment = $api->payment->fetch($input['razorpay_payment_id']);

        if (count($input) && !empty($input['razorpay_payment_id'])) {
            try {
                $response = $api->payment->fetch($input['razorpay_payment_id'])->capture(['amount' => $payment['amount']]);
                $payId = $response->id;

                $auth_user = Auth::guard('web')->user();
                $order = $this->create_order($auth_user, 'Razorpay', 'success', $payId, $customerInfo);

                return $this->successRedirect($order);
            } catch (Exception $e) {
                Log::info('Razorpay payment : ' . $e->getMessage());
            }
        }

        return redirect()->back()->with([
            'message' => trans('translate.Something went wrong, please try again'),
            'alert-type' => 'error'
        ]);
    }

    public function flutterwave_payment(Request $request)
    {
        $customerInfo = $this->customerInfo($request);

        $curl = curl_init();
        $tnx_id = $request->tnx_id;
        $url = "https://api.flutterwave.com/v3/transactions/$tnx_id/verify";
        $token = $this->payment_setting->flutterwave_secret_key;
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: Bearer $token"
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response);

        if ($response->status == 'success') {
            $auth_user = Auth::guard('web')->user();
            $order = $this->create_order($auth_user, 'Flutterwave', 'success', $tnx_id, $customerInfo);

            return response()->json([
                'status' => 'success',
                'message' => trans('translate.Your payment has been made successful. Thanks for your new purchase'),
                'redirect_url' => $order->agency_user_id ? route('agency.tourbooking.bookings.index') : route('user.bookings.index'),
            ]);
        }

        return response()->json([
            'status' => 'faild',
            'message' => trans('translate.Something went wrong, please try again')
        ]);
    }

    public function paystack_payment(Request $request)
    {
        $customerInfo = $this->customerInfo($request);

        $reference = $request->reference;
        $transaction = $request->tnx_id;
        $secret_key = $this->payment_setting->paystack_secret_key;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/transaction/verify/$reference",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $secret_key",
                "Cache-Control: no-cache",
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        $final_data = json_decode($response);
        if ($final_data->status == true) {
            $auth_user = Auth::guard('web')->user();
            $order = $this->create_order($auth_user, 'Paystack', 'success', $transaction, $customerInfo);

            return response()->json([
                'status' => 'success',
                'message' => trans('translate.Your payment has been made successful. Thanks for your new purchase'),
                'redirect_url' => $order->agency_user_id ? route('agency.tourbooking.bookings.index') : route('user.bookings.index'),
            ]);
        }

        return response()->json([
            'status' => 'faild',
            'message' => trans('translate.Something went wrong, please try again')
        ]);
    }

    public function mollie_payment(Request $request)
    {
        if (config('app.mode') == 'DEMO') {
            return redirect()->back()->with([
                'message' => trans('translate.This Is Demo Version. You Can Not Change Anything'),
                'alert-type' => 'error'
            ]);
        }

        $this->customerInfo($request);
        $this->setCustomerInfoSession($request);

        try {
            $calculate_price = $this->calculate_price();
            $mollie_currency = Currency::findOrFail($this->payment_setting->mollie_currency_id);

            $price = ($calculate_price['total_amount'] ?? 0) * ($mollie_currency->currency_rate ?? 1);
            $price = sprintf('%0.2f', $price);

            $mollie_api_key = $this->payment_setting->mollie_key;
            $currency = strtoupper($mollie_currency->currency_code);

            Mollie::api()->setApiKey($mollie_api_key);

            $payment = Mollie::api()->payments()->create([
                'amount' => [
                    'currency' => $currency,
                    'value' => '' . $price . '',
                ],
                'description' => config('app.name'),
                'redirectUrl' => route('payment.mollie-callback'),
            ]);

            $payment = Mollie::api()->payments()->get($payment->id);
            Session::put('payment_id', $payment->id);

            return redirect($payment->getCheckoutUrl(), 303);
        } catch (Exception $e) {
            Log::info('Mollie payment : ' . $e->getMessage());
            return redirect()->back()->with([
                'message' => trans('translate.Please provide valid mollie api key'),
                'alert-type' => 'error'
            ]);
        }
    }

    public function mollie_callback(Request $request)
    {
        $customerInfo = Session::get('customer_info');

        $mollie_api_key = $this->payment_setting->mollie_key;
        Mollie::api()->setApiKey($mollie_api_key);
        $payment = Mollie::api()->payments->get(session()->get('payment_id'));

        if ($payment->isPaid()) {
            $auth_user = Auth::guard('web')->user();
            $order = $this->create_order($auth_user, 'Mollie', 'success', session()->get('payment_id'), $customerInfo);

            return $this->successRedirect($order);
        }

        return redirect()->back()->with([
            'message' => trans('translate.Something went wrong, please try again'),
            'alert-type' => 'error'
        ]);
    }

    public function instamojo_payment(Request $request)
    {
        if (config('app.mode') == 'DEMO') {
            return redirect()->back()->with([
                'message' => trans('translate.This Is Demo Version. You Can Not Change Anything'),
                'alert-type' => 'error'
            ]);
        }

        $this->customerInfo($request);
        $this->setCustomerInfoSession($request);

        $calculate_price = $this->calculate_price();
        $instamojo_currency = Currency::findOrFail($this->payment_setting->instamojo_currency_id);
        $price = ($calculate_price['total_amount'] ?? 0) * ($instamojo_currency->currency_rate ?? 1);
        $price = round($price, 2);

        try {
            $environment = $this->payment_setting->instamojo_account_mode;
            $api_key = $this->payment_setting->instamojo_api_key;
            $auth_token = $this->payment_setting->instamojo_auth_token;

            $url = $environment == 'Sandbox'
                ? 'https://test.instamojo.com/api/1.1/'
                : 'https://www.instamojo.com/api/1.1/';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url . 'payment-requests/');
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "X-Api-Key:$api_key",
                "X-Auth-Token:$auth_token"
            ]);
            $payload = [
                'purpose' => env("APP_NAME"),
                'amount' => $price,
                'phone' => '918160651749',
                'buyer_name' => Auth::user()->name,
                'redirect_url' => route('payment.instamojo-callback'),
                'send_email' => true,
                'webhook' => 'http://www.example.com/webhook/',
                'send_sms' => true,
                'email' => Auth::user()->email,
                'allow_repeated_payments' => false
            ];
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
            $response = curl_exec($ch);
            curl_close($ch);

            $response = json_decode($response);
            return redirect($response->payment_request->longurl);
        } catch (Exception $e) {
            Log::info('Instamojo payment : ' . $e->getMessage());
            return redirect()->back()->with([
                'message' => trans('translate.Something went wrong, please try again'),
                'alert-type' => 'error'
            ]);
        }
    }

    public function instamojo_callback(Request $request)
    {
        $customerInfo = Session::get('customer_info');

        $environment = $this->payment_setting->instamojo_account_mode;
        $api_key = $this->payment_setting->instamojo_api_key;
        $auth_token = $this->payment_setting->instamojo_auth_token;

        $url = $environment == 'Sandbox'
            ? 'https://test.instamojo.com/api/1.1/'
            : 'https://www.instamojo.com/api/1.1/';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . 'payments/' . $request->get('payment_id'));
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "X-Api-Key:$api_key",
            "X-Auth-Token:$auth_token"
        ]);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return redirect()->back()->with([
                'message' => trans('translate.Something went wrong, please try again'),
                'alert-type' => 'error'
            ]);
        }

        $data = json_decode($response);

        if ($data->success == true && $data->payment->status == 'Credit') {
            $auth_user = Auth::guard('web')->user();
            $order = $this->create_order($auth_user, 'Instamojo', 'success', $request->get('payment_id'), $customerInfo);

            return $this->successRedirect($order);
        }

        return redirect()->back()->with([
            'message' => trans('translate.Something went wrong, please try again'),
            'alert-type' => 'error'
        ]);
    }

    public function bank_payment(Request $request)
    {
        $request->validate([
            'tnx_info' => 'required|max:255'
        ], [
            'tnx_info.required' => trans('translate.Transaction field is required')
        ]);

        $customerInfo = $this->customerInfo($request);
        $auth_user = Auth::guard('web')->user();

        $order = $this->create_order($auth_user, 'Bank Payment', 'pending', $request->tnx_info, $customerInfo);

        return $this->successRedirect($order, trans('translate.Your payment has been made. please wait for admin payment approval'));
    }

    /* =======================
     * ORDER CREATION
     * ======================= */

    public function create_order($user, $payment_method, $payment_status, $transaction_id, $customerInfo = [])
    {
        $calculate_price = $this->calculate_price();
        $payment_cart = session()->get('payment_cart');
        $service = Service::findOrFail($payment_cart['service_id']);

        // ✅ NEW: agency context from Agency Wizard (redirected to front checkout)
        $agencyCtx = Session::get('agency_booking_context', []);

        // dacă avem age_quantities, ignorăm person_count/child_count
        if (!empty($payment_cart['age_quantities']) && is_array($payment_cart['age_quantities'])) {
            $adults = 0;
            $children = 0;

            $ageQuantities = $payment_cart['age_quantities'];
            $ageConfig     = $payment_cart['age_config'] ?? [];

            foreach ($ageQuantities as $k => $qty) {
                $qty = (int) $qty;
                if ($qty <= 0) continue;
                $key = strtolower((string) $k);

                if (str_contains($key, 'adult') || $key === 'person' || str_contains($key, 'people')) {
                    $adults += $qty;
                    continue;
                }
                if (str_contains($key, 'child') || str_contains($key, 'children') || str_contains($key, 'kid') ||
                    str_contains($key, 'infant') || str_contains($key, 'teen') || str_contains($key, 'youth') || str_contains($key, 'baby')) {
                    $children += $qty;
                    continue;
                }

                // fallback interval vârstă
                if (isset($ageConfig[$k]) && (isset($ageConfig[$k]['min_age']) || isset($ageConfig[$k]['max_age']))) {
                    $min = (int)($ageConfig[$k]['min_age'] ?? 0);
                    if ($min >= 18) $adults += $qty; else $children += $qty;
                } else {
                    $children += $qty;
                }
            }
        } else {
            $adults   = (int)($payment_cart['person_count'] ?? 0);
            $children = (int)($payment_cart['child_count'] ?? 0);
        }

        // reconstruim breakdown dacă nu a fost pus în sesiune
        $age_breakdown = $payment_cart['age_breakdown'] ?? null;
        if (empty($age_breakdown) && !empty($payment_cart['age_quantities']) && !empty($payment_cart['age_config'])) {
            $age_breakdown = [];
            foreach ($payment_cart['age_quantities'] as $key => $qty) {
                $qty = (int) $qty;
                if ($qty <= 0) continue;
                $cfg   = $payment_cart['age_config'][$key] ?? [];
                $label = $cfg['label'] ?? ucfirst((string)$key);
                $price = (float)($cfg['price'] ?? 0);
                $line  = $qty * $price;
                $age_breakdown[] = [
                    'key'   => $key,
                    'label' => $label,
                    'qty'   => $qty,
                    'price' => $price,
                    'line'  => $line,
                ];
            }
        }

        $order = new Booking();

        $order->is_per_person  = $service->is_per_person ?? 0;

        // ✅ prefer Booking::generateBookingCode() if available (your Booking model has it)
        $order->booking_code   = method_exists(Booking::class, 'generateBookingCode')
            ? Booking::generateBookingCode()
            : uniqid();

        $order->service_id = $service->id;

$agencyCtx = Session::get('agency_booking_context', []);
$bookAsAgency = !empty($agencyCtx['agency_user_id']) && !empty($agencyCtx['agency_client_id']);

if ($bookAsAgency) {
    // ✅ IMPORTANT: ca să nu apară în user dashboard
    $order->user_id = null;

    $order->agency_user_id   = (int) $agencyCtx['agency_user_id'];
    $order->agency_client_id = (int) $agencyCtx['agency_client_id'];
} else {
    // default: user booking
    $order->user_id = $user->id;
}

        $order->adults         = $adults;
        $order->children       = $children;

        // ✅ optional: set infants if column exists + we have age_quantities (baby/infant)
        if (!empty($payment_cart['age_quantities']) && is_array($payment_cart['age_quantities'])) {
            $infants = 0;
            foreach ($payment_cart['age_quantities'] as $k => $qty) {
                $key = strtolower((string) $k);
                if (str_contains($key, 'infant') || str_contains($key, 'baby')) {
                    $infants += (int) $qty;
                }
            }
            if (Schema::hasColumn($order->getTable(), 'infants')) {
                $order->infants = $infants;
            }
        }

        // ✅ optional: save selected date(s) if you store them in bookings table
        if (Schema::hasColumn($order->getTable(), 'check_in_date') && !empty($payment_cart['check_in_date'])) {
            $order->check_in_date = $payment_cart['check_in_date'];
        }
        $order->booking_date = $payment_cart['check_in_date'] ?? null;
        if (Schema::hasColumn($order->getTable(), 'check_out_date') && !empty($payment_cart['check_out_date'])) {
            $order->check_out_date = $payment_cart['check_out_date'];
        }
        if (Schema::hasColumn($order->getTable(), 'availability_id') && !empty($payment_cart['availability_id'])) {
            $order->availability_id = $payment_cart['availability_id'];
        }

        // prețuri de referință (fallback)
        $order->service_price  = $service?->discount_price ?? $service?->full_price ?? 0;
        $order->adult_price    = $service->price_per_person ?? 0;
        $order->child_price    = $service->child_price ?? 0;

        $order->extra_charges  = $payment_cart['extra_charges'] ?? 0;
        $order->extra_services = $payment_cart['extra_services'] ?? [];

        // cupon + sume
        $order->discount_amount = $calculate_price['coupon_amount'] ?? 0;
        $order->subtotal        = $calculate_price['sub_total_amount'] ?? 0;
        $order->total           = $calculate_price['total_amount'] ?? 0;

        // ✅ paid/due amounts (kept compatible)
        $order->paid_amount     = ($payment_status === 'success') ? ($calculate_price['total_amount'] ?? 0) : 0;
        $order->due_amount      = max(0, ($order->total - $order->paid_amount));

        $order->payment_method  = $payment_method;

        // NOTE: keeping your existing mapping so we don't break anything downstream
        $order->booking_status  = $payment_status === 'success' ? 'success' : 'pending';
        $order->payment_status  = $payment_status;

        // ✅ customer info override from agency context (if present)
        if (!empty($agencyCtx)) {
            $customerInfo['customer_name']    = $agencyCtx['customer_name']    ?? ($customerInfo['customer_name']    ?? '');
            $customerInfo['customer_email']   = $agencyCtx['customer_email']   ?? ($customerInfo['customer_email']   ?? '');
            $customerInfo['customer_phone']   = $agencyCtx['customer_phone']   ?? ($customerInfo['customer_phone']   ?? '');
            $customerInfo['customer_address'] = $agencyCtx['customer_address'] ?? ($customerInfo['customer_address'] ?? '');
        }

        $order->customer_name    = $customerInfo['customer_name'] ?? '';
        $order->customer_email   = $customerInfo['customer_email'] ?? '';
        $order->customer_phone   = $customerInfo['customer_phone'] ?? '';
        $order->customer_address = $customerInfo['customer_address'] ?? '';

        // persistăm distribuția pe vârste
        $order->age_quantities = $payment_cart['age_quantities'] ?? null;
        $order->age_config     = $payment_cart['age_config'] ?? null;
        $order->age_breakdown  = $age_breakdown;

        // Store pickup point information
        $order->pickup_point_id = $payment_cart['pickup_point_id'] ?? null;
        $order->pickup_charge = $payment_cart['pickup_charge'] ?? 0;
        $order->pickup_point_name = $payment_cart['pickup_point_name'] ?? null;
        
       
/**
 * ✅ Commission amount (DOAR dacă e booking as agency)
 * - commission_per_sale este % (ex: 90)
 * - commission_amount este suma (total * % / 100)
 */
if ($bookAsAgency) {
    $pct = $this->getCommissionPerSalePercent();
    $commission = round(((float) $order->total) * $pct / 100, 2);

    // în DB ai coloana commission_amount (din dump)
    if (Schema::hasColumn($order->getTable(), 'commission_amount')) {
        $order->commission_amount = $commission;
    }
    // fallback dacă există typo în unele instanțe
    elseif (Schema::hasColumn($order->getTable(), 'comission_amount')) {
        $order->comission_amount = $commission;
    }
} else {
    // opțional: forțăm 0 pe non-agency (dacă vrei curățenie)
    if (Schema::hasColumn($order->getTable(), 'commission_amount')) {
        $order->commission_amount = 0;
    } elseif (Schema::hasColumn($order->getTable(), 'comission_amount')) {
        $order->comission_amount = 0;
    }
}


        $order->save();

        // istoric cupon
        if (Session::get('coupon_code') && Session::get('offer_percentage')) {
            $coupon_history = new CouponController();
            $coupon_history->store_coupon_history($user->id, $calculate_price['coupon_amount'] ?? 0, Session::get('coupon_code'));
        }

        // curățăm sesiunea
        session()->forget('payment_cart');
        session()->forget('customer_info');
        session()->forget('coupon_code');
        session()->forget('offer_percentage');

        // ✅ NEW: cleanup agency context so it doesn't leak into next booking
        session()->forget('agency_booking_context');

        return $order;
    }

    /* =======================
     * HELPERS
     * ======================= */

    /**
     * Redirect to the correct dashboard after a successful payment.
     * Agency bookings go to agency dashboard; regular bookings go to user dashboard.
     */
    private function successRedirect($order, ?string $message = null)
    {
        $message = $message ?? trans('translate.Your payment has been made successful. Thanks for your new purchase');

        $route = !empty($order->agency_user_id)
            ? 'agency.tourbooking.bookings.index'
            : 'user.bookings.index';

        return redirect()->route($route)->with([
            'message'    => $message,
            'alert-type' => 'success',
        ]);
    }

     /**
 * Ia ultima valoare pentru un key din global_settings.
 */
private function getGlobalSettingValue(string $key, $default = null)
{
    $val = DB::table('global_settings')
        ->where('key', $key)
        ->orderByDesc('id')
        ->value('value');

    return $val !== null ? $val : $default;
}

/**
 * Commission per sale (%) din global_settings.
 * Exemplu: 90 => 90%
 */
private function getCommissionPerSalePercent(): float
{
    $val = $this->getGlobalSettingValue('commission_per_sale', 0);
    return max(0.0, (float) $val);
}
     
     
    private function setAgencyBookingContextFromRequest(Request $request, $user): void
{
    Session::forget('agency_booking_context');

    if (!$user) return;

    $bookAsAgency = (int) $request->input('book_as_agency', 0) === 1;
    if (!$bookAsAgency) return;

    $isAgencyEnabled = (int) ($user->is_seller ?? 0) === 1;
    $isApproved = ($user->instructor_joining_request ?? null) === 'approved';
    if (!($isAgencyEnabled && $isApproved)) return;

    $agencyUserId = (int) $user->id;

    $clientId = (int) $request->input('agency_client_id', 0);
    $newClient = (int) $request->input('agency_new_client', 0) === 1;

    // 1) existing client
    if (!$newClient && $clientId > 0) {
        $exists = DB::table('agency_clients')
            ->where('id', $clientId)
            ->where('agency_user_id', $agencyUserId)
            ->whereNull('deleted_at')
            ->exists();

        if (!$exists) {
            $clientId = 0;
        }
    }

    // 2) create new client
    if ($newClient) {
        $first = trim((string) $request->input('agency_first_name', ''));
        $last  = trim((string) $request->input('agency_last_name', ''));

        if ($first === '' || $last === '') {
            // dacă nu are minimul necesar, nu setăm agency context
            return;
        }

        $payload = [
            'agency_user_id' => $agencyUserId,
            'first_name'     => $first,
            'last_name'      => $last,
            'email'          => $request->input('agency_email'),
            'phone'          => $request->input('agency_phone'),
            'country'        => $request->input('agency_country'),
            'state'          => $request->input('agency_state'),
            'city'           => $request->input('agency_city'),
            'address'        => $request->input('agency_address'),
            'notes'          => $request->input('agency_notes'),
            'created_at'     => Carbon::now(),
            'updated_at'     => Carbon::now(),
        ];

        $clientId = (int) DB::table('agency_clients')->insertGetId($payload);
    }

    if ($clientId <= 0) {
        // book_as_agency bifat, dar fără client valid -> nu setăm context
        return;
    }

    $client = DB::table('agency_clients')
        ->where('id', $clientId)
        ->where('agency_user_id', $agencyUserId)
        ->first();

    if (!$client) return;

    $customerName = trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? ''));

    Session::put('agency_booking_context', [
        'agency_user_id'    => $agencyUserId,
        'agency_client_id'  => (int) $clientId,
        'customer_name'     => $customerName ?: ($request->customer_name ?? ''),
        'customer_email'    => $client->email ?? ($request->customer_email ?? ''),
        'customer_phone'    => $client->phone ?? ($request->customer_phone ?? ''),
        'customer_address'  => $client->address ?? ($request->customer_address ?? ''),
    ]);
}

    public function calculate_price()
    {
        $payment_cart = session()->get('payment_cart', []);

        // "total" din sesiune = subtotal (înainte de cupon)
        $sub_total_amount = (float)($payment_cart['total'] ?? 0);
        $coupon_amount = 0;

        if (Session::get('coupon_code') && Session::get('offer_percentage')) {
            $offer_percentage = (float) Session::get('offer_percentage');
            $coupon_amount = ($offer_percentage / 100) * $sub_total_amount;
        }

        $total_amount = $sub_total_amount - $coupon_amount;

        return [
            'sub_total_amount' => $sub_total_amount,
            'coupon_amount'    => $coupon_amount,
            'total_amount'     => $total_amount,
        ];
    }

    public function customerInfo($request)
    {
        // ✅ NEW: capture agency context (if checkbox is checked)
    $this->captureAgencyContextFromRequest($request);

        
        $auth_user = Auth::guard('web')->user();

        return [
            'customer_name'    => $request->customer_name    ?? ($auth_user->name    ?? ''),
            'customer_email'   => $request->customer_email   ?? ($auth_user->email   ?? ''),
            'customer_phone'   => $request->customer_phone   ?? ($auth_user->phone   ?? ''),
            'customer_address' => $request->customer_address ?? ($auth_user->address ?? '')
        ];
    }

    public function setCustomerInfoSession($request)
    {
        // ✅ NEW: capture agency context (if checkbox is checked)
    $this->captureAgencyContextFromRequest($request);
        
        session()->forget('customer_info');

        $auth_user = Auth::guard('web')->user();

        session()->put('customer_info', [
            'customer_name'    => $request->customer_name    ?? ($auth_user->name    ?? ''),
            'customer_email'   => $request->customer_email   ?? ($auth_user->email   ?? ''),
            'customer_phone'   => $request->customer_phone   ?? ($auth_user->phone   ?? ''),
            'customer_address' => $request->customer_address ?? ($auth_user->address ?? '')
        ]);
    }
}
<?php

namespace Modules\PaymentGateway\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayURequest extends FormRequest
{
    /**
     * Determină dacă utilizatorul are permisiunea de a face această cerere.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reguli de validare pentru PayU.
     */
    public function rules(): array
    {
        return [
            'merchant_pos_id' => 'required|string|max:255',
            'secret_key' => 'required|string|max:255',
            'client_id' => 'required|string|max:255',
            'client_secret' => 'required|string|max:255',
            'currency_id' => 'required|string|max:10',
            'sandbox' => 'nullable|boolean',
            'status' => 'nullable|boolean',
            'image' => 'nullable|image|max:2048', // până la 2MB
        ];
    }
    protected function prepareForValidation()
{
    $this->merge([
        'status' => $this->has('status') ? 1 : 0,
        'sandbox' => $this->has('sandbox') ? 1 : 0,
    ]);
}


    /**
     * Mesaje de eroare personalizate.
     */
    public function messages(): array
    {
        return [
            'merchant_pos_id.required' => 'ID-ul PayU Merchant POS este obligatoriu.',
            'secret_key.required' => 'Cheia secretă PayU este obligatorie.',
            'client_id.required' => 'Client ID PayU este obligatoriu.',
            'client_secret.required' => 'Client Secret PayU este obligatoriu.',
            'currency_id.required' => 'Moneda este obligatorie.',
            'image.image' => 'Logo-ul trebuie să fie o imagine validă.',
            'image.max' => 'Imaginea nu poate depăși 2MB.',
        ];
    }
}

<?php

declare(strict_types=1);

namespace Modules\TourBooking\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * ExtraCharge
 *
 * Represents an optional add-on charge for a tour service (e.g., equipment rental).
 *
 * @package Modules\TourBooking\App\Models
 */
final class ExtraCharge extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes.
     *
     * NOTĂ:
     *  - păstrăm câmpurile vechi (price, price_type) ca să nu stricăm nimic existent.
     *  - adăugăm noile câmpuri pentru pricing pe categorii de vârstă + preț general.
     */
    protected $fillable = [
        'service_id',
        'name',
        'description',

        // LEGACY pricing (folosit de sistemul original)
        'price',
        'price_type',

        // NEW pricing model
        'general_price',          // preț general pe trip (dacă NU folosim age-based)
        'adult_price',            // preț / adult
        'child_price',            // preț / child
        'infant_price',           // preț / infant

        // Aplicare la toți participanții (front-end logic)
        'apply_to_all_persons',   // dacă e 1, în front NU mai selectezi bucăți per categorie

        // Alte flag-uri / meta
        'is_mandatory',           // extra obligatoriu (nu poate fi deselectat)
        'is_tax',
        'tax_percentage',
        'max_quantity',
        'status',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        // legacy
        'price'           => 'decimal:2',
        'price_type'      => 'string',

        // new pricing
        'general_price'   => 'decimal:2',
        'adult_price'     => 'decimal:2',
        'child_price'     => 'decimal:2',
        'infant_price'    => 'decimal:2',

        'tax_percentage'        => 'decimal:2',
        'is_mandatory'          => 'boolean',
        'apply_to_all_persons'  => 'boolean',
        'is_tax'                => 'boolean',
        'max_quantity'          => 'integer',
        'status'                => 'boolean',
    ];

    /**
     * Relația cu Service.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Scope: active extra charges.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope: mandatory extra charges.
     */
    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }

    /**
     * Scope: tax charges.
     */
    public function scopeTax($query)
    {
        return $query->where('is_tax', true);
    }

    /**
     * Scope: folosesc pricing general (nu age-based).
     */
    public function scopeGeneralPrice($query)
    {
        return $query->whereNotNull('general_price')
                     ->where(function ($q) {
                         $q->whereNull('adult_price')
                           ->whereNull('child_price')
                           ->whereNull('infant_price');
                     });
    }

    /**
     * Scope: folosesc pricing pe categorii de vârstă.
     */
    public function scopeAgeBased($query)
    {
        return $query->where(function ($q) {
            $q->whereNotNull('adult_price')
              ->orWhereNotNull('child_price')
              ->orWhereNotNull('infant_price');
        });
    }

    /**
     * Helpers pentru front / logic:
     */
    public function usesAgeBasedPricing(): bool
    {
        return !is_null($this->adult_price)
            || !is_null($this->child_price)
            || !is_null($this->infant_price);
    }

    public function usesGeneralPricing(): bool
    {
        return !is_null($this->general_price) && !$this->usesAgeBasedPricing();
    }

    /**
     * Afișarea tipului de preț în admin (coloana "Price Type").
     *
     * - Dacă avem prețuri pe vârstă -> "Per age category"
     * - Dacă avem doar general_price -> "General / flat"
     * - Dacă folosim încă sistema veche -> mapăm price_type ca înainte
     */
    public function getPriceTypeTextAttribute(): string
    {
        // Nou: age-based
        if ($this->usesAgeBasedPricing()) {
            return 'Per age category';
        }

        // Nou: general price
        if ($this->usesGeneralPricing()) {
            return 'General / flat';
        }

        // Legacy fallback pe price_type
        return match($this->price_type) {
            'per_booking' => 'Per Booking',
            'per_person'  => 'Per Person',
            'per_adult'   => 'Per Adult',
            'per_child'   => 'Per Child',
            'per_infant'  => 'Per Infant',
            'per_night'   => 'Per Night',
            'flat'        => 'Flat Fee',
            default       => 'Unknown',
        };
    }
}
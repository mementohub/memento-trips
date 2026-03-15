<?php

declare(strict_types=1);

namespace Modules\TourBooking\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * PickupPoint
 *
 * Represents a pickup/meeting point location for a tour service.
 *
 * @package Modules\TourBooking\App\Models
 */
final class PickupPoint extends Model
{
    use HasFactory;

    /**
     * Attributes assignable in mass.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'service_id',
        'name',
        'description',
        'address',
        'latitude',
        'longitude',
        'extra_charge',
        'charge_type',
        'age_category_prices',
        'is_default',
        'status',
        'notes',
    ];

    /**
     * Type casts.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude'             => 'decimal:8',
        'longitude'            => 'decimal:8',
        'extra_charge'         => 'decimal:2',
        'age_category_prices'  => 'array',
        'is_default'           => 'boolean',
        'status'               => 'boolean',
    ];

    /* =========================================================
     |  Relationships
     * =======================================================*/

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /* =========================================================
     |  Scopes
     * =======================================================*/

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeForService($query, int $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeWithExtraCharge($query)
    {
        return $query->whereNotNull('extra_charge')->where('extra_charge', '>', 0);
    }

    public function scopeFree($query)
    {
        return $query->where(function($q) {
            $q->whereNull('extra_charge')->orWhere('extra_charge', 0);
        });
    }

    /* =========================================================
     |  Helpers
     * =======================================================*/

    /**
     * Check if this pickup point has extra charges.
     */
    public function hasExtraCharge(): bool
    {
        return $this->extra_charge !== null && $this->extra_charge > 0;
    }

    /**
     * Get formatted extra charge.
     */
    public function getFormattedExtraChargeAttribute(): string
    {
        if ($this->charge_type === 'per_person') {
            $prices = $this->age_category_prices ?? [];
            if (empty($prices)) {
                return __('translate.Per Person') . ' (' . __('translate.No prices set') . ')';
            }
            $parts = [];
            foreach ($prices as $cat => $price) {
                if ($price !== null && (float)$price > 0) {
                    $parts[] = ucfirst($cat) . ': ' . currency((float)$price);
                }
            }
            return empty($parts)
                ? __('translate.Free')
                : implode(' | ', $parts);
        }

        // per_booking (flat)
        if (!$this->hasExtraCharge()) {
            return __('translate.Free');
        }
        return currency($this->extra_charge);
    }

    /**
     * Calculate distance from given coordinates (in kilometers).
     */
    public function distanceFrom(float $lat, float $lng): float
    {
        $earthRadius = 6371; // km

        $deltaLat = deg2rad($lat - $this->latitude);
        $deltaLng = deg2rad($lng - $this->longitude);

        $a = sin($deltaLat/2) * sin($deltaLat/2) +
             cos(deg2rad($this->latitude)) * cos(deg2rad($lat)) *
             sin($deltaLng/2) * sin($deltaLng/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return round($earthRadius * $c, 2);
    }

    /**
     * Get coordinates as array.
     */
    public function getCoordinatesAttribute(): array
    {
        return [
            'lat' => (float) $this->latitude,
            'lng' => (float) $this->longitude,
        ];
    }

    /**
     * Calculate extra charge based on charge type and quantities.
     */
    public function calculateExtraCharge(array $quantities = []): float
    {
        if ($this->charge_type === 'per_person') {
            $prices = $this->age_category_prices ?? [];
            if (empty($prices)) {
                return 0.0;
            }
            $total = 0.0;
            foreach (['adult', 'child', 'baby', 'infant'] as $cat) {
                $catPrice = (float)($prices[$cat] ?? 0);
                $catQty   = (int)($quantities[$cat] ?? 0);
                $total   += $catPrice * $catQty;
            }
            return $total;
        }

        // per_booking — flat rate
        if (!$this->hasExtraCharge()) {
            return 0.0;
        }
        return (float)$this->extra_charge;
    }
}

<?php

declare(strict_types=1);

namespace Modules\TourBooking\App\Models;

use App\Models\User;
use App\Models\AgencyClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Booking
 *
 * Represents a tour booking with guest details, pricing, payment status, and commission data.
 *
 * @package Modules\TourBooking\App\Models
 */
final class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_code',
        'service_id',
        'user_id',

        // NEW (Agency CRM)
        'agency_user_id',
        'agency_client_id',
        'commission_amount',

        'adults',
        'children',
        'infants',
        'service_price',
        'child_price',
        'adult_price',
        'infant_price',
        'extra_charges',
        'discount_amount',
        'tax_amount',
        'subtotal',
        'total',
        'paid_amount',
        'due_amount',
        'extra_services',
        'coupon_code',
        'payment_method',
        'payment_status',
        'booking_status',
        'customer_notes',
        'admin_notes',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'custom_fields',
        'cancellation_reason',
        'confirmed_at',
        'cancelled_at',
        'completed_at',
        'is_reviewed',
        'meta_data',
        'is_per_person',
        'pickup_point_id',
        'pickup_charge',
        'pickup_point_name',
        'age_quantities',
        'age_config',
        'age_breakdown',
    ];

    protected $casts = [
        'adults' => 'integer',
        'children' => 'integer',
        'infants' => 'integer',
        'service_price' => 'decimal:2',
        'adult_price' => 'decimal:2',
        'child_price' => 'decimal:2',
        'infant_price' => 'decimal:2',
        'extra_charges' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'pickup_charge' => 'decimal:2',

        // NEW
        'commission_amount' => 'decimal:2',

        'extra_services' => 'json',
        'custom_fields' => 'json',
        'meta_data' => 'json',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_reviewed' => 'boolean',
        'is_per_person' => 'boolean',
        'age_quantities' => 'array',
        'age_config'     => 'array',
        'age_breakdown'  => 'array',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // NEW: agency user (owner of CRM booking)
    public function agencyUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agency_user_id');
    }

    // NEW: agency client
    public function agencyClient(): BelongsTo
    {
        return $this->belongsTo(AgencyClient::class, 'agency_client_id');
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function pickupPoint(): BelongsTo
    {
        return $this->belongsTo(PickupPoint::class);
    }

    public function getDurationInDaysAttribute(): int
    {
        if (!$this->check_out_date) {
            return 1;
        }

        return $this->check_in_date->diffInDays($this->check_out_date) ?: 1;
    }

    public function getTotalGuestsAttribute(): int
    {
        return (int) $this->adults + (int) $this->children + (int) $this->infants;
    }

    public function scopePending($query)
    {
        return $query->where('booking_status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('booking_status', 'confirmed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('booking_status', 'cancelled');
    }

    public function scopeCompleted($query)
    {
        return $query->where('booking_status', 'completed');
    }

    public function scopePaymentPending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopePaymentCompleted($query)
    {
        return $query->whereIn('payment_status', ['completed', 'success']);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->where(function($q) use ($startDate, $endDate) {
            $q->whereBetween('check_in_date', [$startDate, $endDate])
              ->orWhereBetween('check_out_date', [$startDate, $endDate])
              ->orWhere(function($query) use ($startDate, $endDate) {
                  $query->where('check_in_date', '<=', $startDate)
                        ->where('check_out_date', '>=', $endDate);
              });
        });
    }

    public function scopeForService($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // NEW helper scope
    public function scopeForAgency($query, $agencyUserId)
    {
        return $query->where('agency_user_id', $agencyUserId);
    }

    public static function generateBookingCode(): string
    {
        $prefix = 'BK';
        $uniqueCode = $prefix . strtoupper(substr(uniqid(), -6)) . rand(10, 99);

        while (self::where('booking_code', $uniqueCode)->exists()) {
            $uniqueCode = $prefix . strtoupper(substr(uniqid(), -6)) . rand(10, 99);
        }

        return $uniqueCode;
    }
}
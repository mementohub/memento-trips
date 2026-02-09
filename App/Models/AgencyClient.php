<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\TourBooking\App\Models\Booking;

class AgencyClient extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'agency_user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'country',
        'state',
        'city',
        'address',
        'notes',
        'lawful_basis',
        'consent_email_marketing_at',
        'privacy_notice_version',
    ];

    protected $casts = [
        'consent_email_marketing_at' => 'datetime',
    ];

    public function agencyUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agency_user_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'agency_client_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }
}
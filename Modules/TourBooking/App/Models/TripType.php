<?php

declare(strict_types=1);

namespace Modules\TourBooking\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * TripType
 *
 * Represents a trip type classification (e.g., Adventure, Cultural, Family).
 *
 * @package Modules\TourBooking\App\Models
 */
final class TripType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'status',
        'is_featured',
        'show_on_homepage',
        'display_order',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_featured' => 'boolean',
        'show_on_homepage' => 'boolean',
    ];

    /**
     * The services associated with this trip type.
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class , 'service_trip_type');
    }
}
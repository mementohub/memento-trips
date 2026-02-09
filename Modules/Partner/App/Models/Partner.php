<?php

namespace Modules\Partner\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Partner\Database\factories\PartnerFactory;

/**
 * Partner
 *
 * Represents a partner/sponsor with logo and link.
 *
 * @package Modules\Partner\App\Models
 */
class Partner extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

}

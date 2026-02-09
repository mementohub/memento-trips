<?php

namespace Modules\Coupon\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Coupon\Database\factories\CouponFactory;

/**
 * Coupon
 *
 * Represents a discount coupon with code, amount, validity dates, and usage limits.
 *
 * @package Modules\Coupon\App\Models
 */
class Coupon extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

}

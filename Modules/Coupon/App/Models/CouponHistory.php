<?php

namespace Modules\Coupon\App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Coupon\Database\factories\CouponHistoryFactory;

/**
 * CouponHistory
 *
 * Tracks coupon usage history — records which users applied which coupons.
 *
 * @package Modules\Coupon\App\Models
 */
class CouponHistory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    public function buyer(){
        return $this->belongsTo(User::class, 'buyer_id')->select('id', 'name', 'email');
    }

    public function coupon(){
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    
}

<?php

namespace Modules\PaymentWithdraw\App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\PaymentWithdraw\Database\factories\SellerWithdrawFactory;

/**
 * SellerWithdraw
 *
 * Represents an agency withdrawal/payout request with amount, method, and status.
 *
 * @package Modules\PaymentWithdraw\App\Models
 */
class SellerWithdraw extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    public function seller(){
        return $this->belongsTo(User::class, 'seller_id');
    }
}

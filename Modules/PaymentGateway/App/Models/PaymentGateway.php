<?php

namespace Modules\PaymentGateway\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\PaymentGateway\Database\factories\PaymentGatewayFactory;

/**
 * PaymentGateway
 *
 * Key-value store for payment gateway credentials and settings.
 *
 * @package Modules\PaymentGateway\App\Models
 */
class PaymentGateway extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    
}

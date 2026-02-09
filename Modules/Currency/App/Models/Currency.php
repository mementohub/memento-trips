<?php

namespace Modules\Currency\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Currency\Database\factories\CurrencyFactory;

/**
 * Currency
 *
 * Represents a supported currency with exchange rate, symbol, and display position.
 *
 * @package Modules\Currency\App\Models
 */
class Currency extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

}

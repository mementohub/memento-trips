<?php

namespace Modules\Newsletter\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Newsletter\Database\factories\NewsletterFactory;

/**
 * Newsletter
 *
 * Stores newsletter subscriber email addresses.
 *
 * @package Modules\Newsletter\App\Models
 */
class Newsletter extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];
    

}

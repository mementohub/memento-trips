<?php

namespace Modules\ContactMessage\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\ContactMessage\Database\factories\ContactMessageFactory;

/**
 * ContactMessage
 *
 * Stores contact form submissions from the frontend.
 *
 * @package Modules\ContactMessage\App\Models
 */
class ContactMessage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];


}

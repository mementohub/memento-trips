<?php

namespace Modules\Page\App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Page\Database\factories\ContactUsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * ContactUs
 *
 * Stores the Contact Us page configuration and content.
 *
 * @package Modules\Page\App\Models
 */
class ContactUs extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

}

<?php

namespace Modules\Language\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Language\Database\factories\LanguageFactory;

/**
 * Language
 *
 * Represents a supported language with code, direction, and active status.
 *
 * @package Modules\Language\App\Models
 */
class Language extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];
    
    
}

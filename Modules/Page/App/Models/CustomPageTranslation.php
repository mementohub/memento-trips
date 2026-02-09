<?php

namespace Modules\Page\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Page\Database\factories\CustomPageTranslationFactory;

/**
 * CustomPageTranslation
 *
 * Stores translated content for custom pages.
 *
 * @package Modules\Page\App\Models
 */
class CustomPageTranslation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];



}

<?php

namespace Modules\Page\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Page\Database\factories\FooterTranslationFactory;

/**
 * FooterTranslation
 *
 * Stores translated content for footer sections.
 *
 * @package Modules\Page\App\Models
 */
class FooterTranslation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];


}

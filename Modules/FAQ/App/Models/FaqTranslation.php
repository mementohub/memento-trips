<?php

namespace Modules\FAQ\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\FAQ\Database\factories\FaqTranslationFactory;

/**
 * FaqTranslation
 *
 * Stores translated content for FAQ entries (question, answer).
 *
 * @package Modules\FAQ\App\Models
 */
class FaqTranslation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    protected static function newFactory(): FaqTranslationFactory
    {

    }
}

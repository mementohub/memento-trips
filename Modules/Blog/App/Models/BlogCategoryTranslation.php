<?php

namespace Modules\Blog\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Blog\Database\factories\BlogCategoryTranslationFactory;

/**
 * BlogCategoryTranslation
 *
 * Stores translated content for blog categories (name, slug).
 *
 * @package Modules\Blog\App\Models
 */
class BlogCategoryTranslation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

}

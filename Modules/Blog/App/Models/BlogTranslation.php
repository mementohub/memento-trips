<?php

namespace Modules\Blog\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Blog\Database\factories\BlogTranslationFactory;

/**
 * BlogTranslation
 *
 * Stores translated content for blog posts (title, description, SEO fields).
 *
 * @package Modules\Blog\App\Models
 */
class BlogTranslation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

}

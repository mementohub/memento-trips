<?php

namespace Modules\Testimonial\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Testimonial\Database\factories\TestimonialTrasnlationFactory;

/**
 * TestimonialTrasnlation
 *
 * Stores translated content for testimonials.
 *
 * @package Modules\Testimonial\App\Models
 */
class TestimonialTrasnlation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

}

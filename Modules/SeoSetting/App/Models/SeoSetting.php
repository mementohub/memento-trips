<?php

namespace Modules\SeoSetting\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\SeoSetting\Database\factories\SeoSettingFactory;

/**
 * SeoSetting
 *
 * Key-value store for global SEO configuration (meta tags, analytics codes).
 *
 * @package Modules\SeoSetting\App\Models
 */
class SeoSetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

}

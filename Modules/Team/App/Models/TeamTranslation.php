<?php

namespace Modules\Team\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * TeamTranslation
 *
 * Stores translated content for team member profiles.
 *
 * @package Modules\Team\App\Models
 */
class TeamTranslation extends Model
{
    protected $guarded = [];

    protected $casts = [
        'skill_list' => 'array'
    ];

}

<?php

namespace Modules\Team\App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Team
 *
 * Represents a team member profile with name, role, image, and social links.
 *
 * @package Modules\Team\App\Models
 */
class Team extends Model
{
    protected $guarded = [];

    protected $appends = ['name', 'description', 'designation'];

    public function translate(){
        return $this->belongsTo(TeamTranslation::class, 'id',
            'team_id')
            ->where('lang_code' , admin_lang())
            ->withDefault([
                'name' => '',
                'description' => '',
                'designation' => '',
            ]);
    }

    public function front_translate(){
        return $this->belongsTo(TeamTranslation::class, 'id', 'team_id')->where('lang_code' , front_lang());
    }
}

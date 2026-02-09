<?php

namespace Modules\EmailSetting\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\EmailSetting\Database\factories\EmailSettingFactory;

/**
 * EmailSetting
 *
 * Key-value store for SMTP email configuration (host, port, credentials, sender).
 *
 * @package Modules\EmailSetting\App\Models
 */
class EmailSetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];


}

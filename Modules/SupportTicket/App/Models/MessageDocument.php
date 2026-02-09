<?php

namespace Modules\SupportTicket\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * MessageDocument
 *
 * Represents a file attachment on a support ticket message.
 *
 * @package Modules\SupportTicket\App\Models
 */
class MessageDocument extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

}

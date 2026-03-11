<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminDeleteRequest extends Model
{
    protected $fillable = ['admin_id', 'requested_by', 'status', 'responded_by'];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function requester()
    {
        return $this->belongsTo(Admin::class, 'requested_by');
    }

    public function responder()
    {
        return $this->belongsTo(Admin::class, 'responded_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}

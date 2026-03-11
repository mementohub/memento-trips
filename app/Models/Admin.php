<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use HasFactory;

    const STATUS_ACTIVE = 'enable';
    const STATUS_INACTIVE = 'disable';

    protected $fillable = ['name', 'email', 'password', 'status'];

    protected $hidden = ['password', 'remember_token'];

    public function deleteRequests()
    {
        return $this->hasMany(AdminDeleteRequest::class, 'admin_id');
    }

    public function pendingDeleteRequest()
    {
        return $this->hasOne(AdminDeleteRequest::class, 'admin_id')->where('status', 'pending');
    }
}

<?php

namespace Modules\Wishlist\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Wishlist\Database\factories\WishlistFactory;

/**
 * Wishlist
 *
 * Represents a user's wishlist entry linking a user to a saved tour service.
 *
 * @package Modules\Wishlist\App\Models
 */
class Wishlist extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    public function wishable()
    {
        return $this->morphTo();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Frontend extends Model
{
    protected $fillable = ['data_keys', 'data_values', 'theme_name', 'ordering'];

    protected $casts = [
        'data_values' => 'array',
        'ordering' => 'integer',
    ];

    // Optional: Accessor to parse JSON data_values
    public function getDataValuesAttribute($value)
    {
        return json_decode($value, true);
    }

}

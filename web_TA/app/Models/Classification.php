<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classification extends Model
{
    protected $fillable = [
        'user_id',
        'image_path',
        'filename',
        'predicted_class',
        'confidence',
        'all_predictions',
        'disease_name',
        'severity',
        'notes',
        'location_address',
        'location_lat',
        'location_lng',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'location_lat' => 'float',
        'location_lng' => 'float',
        'all_predictions' => 'array',
        'confidence' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classification extends Model
{
    protected $fillable = [
        'image_path',
        'filename',
        'predicted_class',
        'confidence',
        'all_predictions',
        'disease_name',
        'severity',
        'notes',
    ];

    protected $casts = [
        'all_predictions' => 'array',
        'confidence' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

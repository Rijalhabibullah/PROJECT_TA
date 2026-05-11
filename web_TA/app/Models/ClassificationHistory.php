<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassificationHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'jenis_penyakit',
        'location_address',
        'location_lat',
        'location_lng',
        'kabupaten',
        'kecamatan',
        'kelurahan',
    ];

    protected $casts = [
        'location_lat' => 'float',
        'location_lng' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

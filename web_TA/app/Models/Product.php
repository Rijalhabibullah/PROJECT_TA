<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // Kolom mana saja yang boleh diisi secara massal (create/update)
    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'image'
    ];
}
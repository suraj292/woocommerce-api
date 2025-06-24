<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'price',
        'image_url',
        'status',
        'wc_product_id',
    ];
    protected $casts = [
        'price' => 'decimal:2',
        'status' => 'string',
    ];
}

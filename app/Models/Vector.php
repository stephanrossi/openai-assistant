<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vector extends Model
{
    protected $fillable = ['file_name', 'embedding'];

    // Casting para garantir que os embeddings sejam tratados como array
    protected $casts = [
        'embedding' => 'array',
    ];
}

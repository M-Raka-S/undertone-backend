<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{HasMany, BelongsTo};

class Parameter extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'identifier',
        'category_id',
    ];

    protected $hidden = [
        'category_id',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function instanceParameters(): HasMany
    {
        return $this->hasMany(InstanceParameter::class);
    }
}

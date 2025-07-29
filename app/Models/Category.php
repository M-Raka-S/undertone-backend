<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function parameters(): HasMany
    {
        return $this->hasMany(Parameter::class);
    }

    public function instances(): HasMany
    {
        return $this->hasMany(CategoryInstance::class);
    }
}

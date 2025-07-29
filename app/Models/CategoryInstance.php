<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{HasMany, BelongsTo};

class CategoryInstance extends Model
{
    use HasFactory;

    protected $fillable = [
        'summary',
        'project_id',
        'category_id',
    ];

    protected $hidden = [
        'project_id',
        'category_id',
    ];

    protected $appends = ['identifier_value'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class, 'instance_id');
    }

    public function parameters(): HasMany
    {
        return $this->hasMany(InstanceParameter::class, 'instance_id');
    }

    public function getIdentifierValueAttribute()
    {
        $parameter = $this->parameters()
            ->whereHas('parameter', function ($query) {
                $query->where('identifier', true);
            })
            ->with('parameter')
            ->first();

        if($parameter) {
            return [
                'value' => $parameter->value,
                'parameter_name' => $parameter->parameter->name,
            ];
        }
    }
}

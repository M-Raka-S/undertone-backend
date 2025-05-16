<?php

namespace App\Models;

use App\Jobs\CopyParameterToInstances;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, BelongsTo, HasMany};

class Parameter extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
    ];

    protected static function boot()
    {
        parent::boot();
        static::created(function (Parameter $parameter) {
            CopyParameterToInstances::dispatch($parameter);
        });
    }

    /**
     * The projects that belong to the Project
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_parameters', 'parameter_id', 'project_id')->withPivot(['id']);
    }

    public function attachProject($project) {
        $this->projects()->attach($project);
    }

    /**
     * Get the category that owns the Parameter
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all of the instanceParameters for the Parameter
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function instanceParameters(): HasMany
    {
        return $this->hasMany(InstanceParameter::class);
    }
}

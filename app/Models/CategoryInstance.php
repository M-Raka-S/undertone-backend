<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoryInstance extends Model
{
    use HasFactory;

    /**
     * Get the category that owns the CategoryInstance
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the project that owns the CategoryInstance
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get all of the parameters for the CategoryInstance
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function parameters(): HasMany
    {
        return $this->hasMany(InstanceParameter::class, "instance_id");
    }
}

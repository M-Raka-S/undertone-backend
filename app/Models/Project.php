<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, HasMany};


class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'hidden_categories',
    ];

    protected $casts = [
        'hidden_categories' => 'array',
    ];

    /**
     * The users that belong to the Project
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_users', 'project_id', 'user_id');
    }

    public function attachUser($user) {
        $this->users()->attach($user);
    }

    /**
     * The parameters that belong to the Project
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function parameters(): BelongsToMany
    {
        return $this->belongsToMany(Parameter::class, 'project_parameters', 'project_id', 'parameter_id')->withPivot(['id']);
    }

    public function attachParameter($parameter) {
        $this->parameters()->attach($parameter);
    }

    /**
     * Get all of the chapters for the Project
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }

    /**
     * Get all of the medias for the Project
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function medias(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    /**
     * Get all of the categoryInstances for the Project
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function categoryInstances(): HasMany
    {
        return $this->hasMany(CategoryInstance::class);
    }
}

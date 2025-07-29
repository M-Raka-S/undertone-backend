<?php

namespace App\Models;

use App\Roles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{HasMany, BelongsToMany};


class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'summary',
        'hidden_categories',
    ];

    protected $casts = [
        'hidden_categories' => 'array',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_users', 'project_id', 'user_id')->withPivot('role');
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }

    public function categoryInstances(): HasMany
    {
        return $this->hasMany(CategoryInstance::class);
    }

    public function detachUser($user)
    {
        $this->users()->detach($user);
    }

    public function attachUser($user, $role = 'editor')
    {
        $this->users()->attach($user, ['role' => $role]);
    }

    public function updateUserRole($user, $role)
    {
        $this->users()->updateExistingPivot($user->id, ['role' => $role]);
    }

    public function getRoleInfo($user)
    {
        $roleValue = $this->users()->where('user_id', $user->id)->first()?->pivot->role;
        if (!$roleValue) {
            return null;
        }
        $roleEnum = Roles::from($roleValue);
        return $roleEnum->info();
    }
}

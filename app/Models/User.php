<?php

namespace App\Models;

use App\Roles;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\{HasMany, BelongsToMany};

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_users', 'user_id', 'project_id')->withPivot('role');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function detachProject($project)
    {
        $this->projects()->detach($project);
    }

    public function attachProject($project, $role = 'editor')
    {
        $this->projects()->attach($project, ['role' => $role]);
    }

    public function getRoleInfo($project)
    {
        $roleValue = $this->projects()->where('project_id', $project->id)->first()?->pivot->role;
        if (!$roleValue) {
            return null;
        }
        $roleEnum = Roles::from($roleValue);
        return $roleEnum->info();
    }
}

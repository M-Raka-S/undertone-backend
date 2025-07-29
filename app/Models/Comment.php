<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasMany, BelongsTo};

class Comment extends Model
{
    protected $fillable = [
        'uuid',
        'content',
        'user_id',
        'parent_id',
        'chapter_id',
    ];

    protected $hidden = [
        'user_id',
        'parent_id',
        'chapter_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->with('user', 'replies');
    }
}

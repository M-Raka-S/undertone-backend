<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'path',
        'instance_id',
        'project_id',
    ];

    /**
     * Get the instanceParameter that owns the Media
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function instance(): BelongsTo
    {
        return $this->belongsTo(CategoryInstance::class, 'instance_id');
    }

    /**
     * Get the project that owns the Media
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}

<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InstanceParameter extends Model
{
    use HasFactory;

    protected $fillable = [
        'value',
        'instance_id',
        'parameter_id',
    ];

    protected $hidden = [
        'instance_id',
        'parameter_id',
    ];

    public function parameter(): BelongsTo
    {
        return $this->belongsTo(Parameter::class);
    }

    public function instance(): BelongsTo
    {
        return $this->belongsTo(CategoryInstance::class, 'instance_id');
    }
}

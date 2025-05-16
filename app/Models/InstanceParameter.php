<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InstanceParameter extends Model
{
    use HasFactory;

    protected $fillable = [
        'value',
        'parameter_id',
        'instance_id',
    ];

    /**
     * Get the parameter that owns the InstanceParameter
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parameter(): BelongsTo
    {
        return $this->belongsTo(Parameter::class);
    }

    /**
     * Get the instance that owns the InstanceParameter
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function instance(): BelongsTo
    {
        return $this->belongsTo(CategoryInstance::class, 'instance_id');
    }
}

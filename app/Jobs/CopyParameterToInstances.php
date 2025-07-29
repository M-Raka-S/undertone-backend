<?php

namespace App\Jobs;

use App\Models\CategoryInstance;
use App\Models\InstanceParameter;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class CopyParameterToInstances implements ShouldQueue
{
    use Queueable;

    protected $parameter;

    public function __construct($parameter)
    {
        $this->parameter = $parameter;
    }

    public function handle(): void
    {
        $instances = CategoryInstance::where('category_id', $this->parameter->category->id)->get();
        if ($instances->count() > 0) {
            $insertData = [];
            foreach ($instances as $instance) {
                $insertData[] = [
                    'parameter_id' => $this->parameter->id,
                    'instance_id' => $instance->id,
                    'value' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            InstanceParameter::insert($insertData);
        }
    }
}

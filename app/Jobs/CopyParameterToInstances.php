<?php

namespace App\Jobs;

use App\Models\CategoryInstance;
use App\Models\InstanceParameter;
use App\Models\Parameter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CopyParameterToInstances implements ShouldQueue
{
    use Queueable;

    protected $parameter;

    /**
     * Create a new job instance.
     */
    public function __construct($parameter)
    {
        $this->parameter = $parameter;
    }

    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $instances = CategoryInstance::all();
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

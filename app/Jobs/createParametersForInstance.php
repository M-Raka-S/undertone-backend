<?php

namespace App\Jobs;

use App\Models\InstanceParameter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class createParametersForInstance implements ShouldQueue
{
    use Queueable;

    protected $instance;

    /**
     * Create a new job instance.
     */
    public function __construct($instance)
    {
        $this->instance = $instance;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $parameters = $this->instance->category->parameters;
        if ($parameters->count() > 0) {
            $insertData = [];
            foreach ($parameters as $parameter) {
                $insertData[] = [
                    'parameter_id' => $parameter->id,
                    'instance_id' => $this->instance->id,
                    'value' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            InstanceParameter::insert($insertData);
            $id = $this->instance->id;
        }
    }
}

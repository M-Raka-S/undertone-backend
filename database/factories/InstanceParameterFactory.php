<?php

namespace Database\Factories;

use App\Models\CategoryInstance;
use App\Models\Parameter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InstanceParameter>
 */
class InstanceParameterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parameter_id' => Parameter::factory(),
            'instance_id' => CategoryInstance::factory(),
        ];
    }
}

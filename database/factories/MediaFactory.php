<?php

namespace Database\Factories;

use App\Models\InstanceParameter;
use App\Models\Project;
use App\Models\ProjectParameter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Media>
 */
class MediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'path' => $this->faker->imageUrl(),
            'category_instance_id' => InstanceParameter::factory(),
            'project_id' => Project::factory(),
        ];
    }
}

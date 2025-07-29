<?php

use App\Models\{User, Project, Chapter, Category, Parameter, ProjectParameter, ParameterInput, Media};
use App\Models\CategoryInstance;
use App\Models\InstanceParameter;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->project = Project::factory()->create();
    $this->category = Category::factory()->create();
});

test('users and projects relationship', function () {
    $this->user->attachProject($this->project->id);
    expect($this->user->projects->first()->id)->toBe($this->project->id)
        ->and($this->project->users->first()->id)->toBe($this->user->id);
    $roleInfo = $this->project->getRoleInfo($this->user);
    expect($roleInfo)
        ->toHaveKey('name', 'editor')
        ->toHaveKey('description', 'Allows only reading and commenting privileges.');
    $this->project->updateUserRole($this->user, 'projectmanager');
    $roleInfo = $this->user->getRoleInfo($this->project);
    expect($roleInfo)
        ->toHaveKey('name', 'project manager')
        ->toHaveKey('description', 'Allows project control without writing privileges.');
});

test('projects and chapters relationship', function () {
    $this->user->attachProject($this->project->id);
    $chapter = Chapter::factory()->create(['project_id' => $this->project->id]);
    expect($chapter->project->id)->toBe($this->project->id)
        ->and($this->project->chapters->first()->id)->toBe($chapter->id);
});

test('categories and parameters relationship', function () {
    [$parameter1, $parameter2] = Parameter::factory()->count(2)->create(['category_id' => $this->category->id]);
    expect($parameter1->category->id)->toBe($this->category->id)
        ->and($parameter2->category->id)->toBe($this->category->id)
        ->and($this->category->parameters->pluck('id'))->toContain($parameter1->id, $parameter2->id);
});

test('category instances relationships', function() {
    [$parameter1, $parameter2] = Parameter::factory()->count(2)->create(['category_id' => $this->category->id]);
    [$instance1, $instance2] = CategoryInstance::factory()->count(2)->create([
        'category_id' => $this->category->id,
        'project_id' => $this->project->id,
    ]);
    [$instance1parameter1, $instance1parameter2] = InstanceParameter::factory()->count(2)->sequence(
        ['instance_id' => $instance1->id, 'parameter_id' => $parameter1->id],
        ['instance_id' => $instance1->id, 'parameter_id' => $parameter2->id]
    )->create();
    [$instance2parameter1, $instance2parameter2] = InstanceParameter::factory()->count(2)->sequence(
        ['instance_id' => $instance2->id, 'parameter_id' => $parameter1->id],
        ['instance_id' => $instance2->id, 'parameter_id' => $parameter2->id]
    )->create();
    expect($instance1->category->id)->toBe($this->category->id)
        ->and($instance1->project->id)->toBe($this->project->id)
        ->and($instance2->category->id)->toBe($this->category->id)
        ->and($instance2->project->id)->toBe($this->project->id)
        ->and($instance1parameter1->instance->id)->toBe($instance1->id)
        ->and($instance1parameter1->parameter->id)->toBe($parameter1->id)
        ->and($instance1parameter2->instance->id)->toBe($instance1->id)
        ->and($instance1parameter2->parameter->id)->toBe($parameter2->id)
        ->and($instance2parameter1->instance->id)->toBe($instance2->id)
        ->and($instance2parameter1->parameter->id)->toBe($parameter1->id)
        ->and($instance2parameter2->instance->id)->toBe($instance2->id)
        ->and($instance2parameter2->parameter->id)->toBe($parameter2->id)
        ->and($this->category->instances->pluck('id'))->toContain($instance1->id, $instance2->id)
        ->and($this->project->categoryInstances->pluck('id'))->toContain($instance1->id, $instance2->id)
        ->and($instance1->parameters->pluck('id'))->toContain($instance1parameter1->id, $instance1parameter2->id)
        ->and($instance2->parameters->pluck('id'))->toContain($instance2parameter1->id, $instance2parameter2->id)
        ->and($parameter1->instanceParameters->pluck('id'))->toContain($instance1parameter1->id, $instance2parameter1->id)
        ->and($parameter2->instanceParameters->pluck('id'))->toContain($instance1parameter2->id, $instance2parameter2->id);
});

test('media relationships', function() {
    $instance = CategoryInstance::factory()->create();
    $project = Project::factory()->create();
    [$media1, $media2] = Media::factory()->count(2)->sequence(
        ['instance_id' => $instance->id],
        ['project_id' => $project->id]
    )->create();
    expect($instance->media->pluck('id'))->toContain($media1->id)
    ->and($project->media->pluck('id'))->toContain($media2->id)
    ->and($media1->instance->id)->toBe($instance->id)
    ->and($media2->project->id)->toBe($project->id);
});

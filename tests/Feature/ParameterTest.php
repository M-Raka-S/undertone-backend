<?php

use App\Models\Category;
use App\Models\CategoryInstance;
use App\Models\InstanceParameter;
use App\Models\Parameter;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->category = Category::factory()->create();
    $this->actingAs(User::factory()->create());
    $this->parameters = Parameter::factory()->count(30)->create(['category_id' => $this->category->id]);
});

test('fails when making parameter without parameters', function () {
    $response = $this->post('/api/parameters');
    $response->assertStatus(422);
});

test('fails when making parameter without category_id parameter', function () {
    $response = $this->post('/api/parameters', [
        'name' => 'Parameter name',
    ]);
    $response->assertStatus(422);
});

test('fails when making parameter without name parameter', function () {
    $response = $this->post('/api/parameters', [
        'category_id' => $this->category->id,
    ]);
    $response->assertStatus(422);
});

test('fails when making parameter with invalid category id', function () {
    $response = $this->post('/api/parameters', [
        'name' => 'Parameter name',
        'category_id' => -1,
    ]);
    $response->assertStatus(422);
});

test('success when making parameter with valid parameters', function () {
    $response = $this->post('/api/parameters', [
        'name' => 'Parameter name',
        'category_id' => $this->category->id,
    ]);
    $response->assertStatus(201);
});

test('copies new parameter to existing instances', function () {
    $project = Project::factory()->create();
    $response = $this->post('/api/instances', [
        'category_id' => $this->category->id,
        'project_id' => $project->id,
    ]);
    $created_id = $response->getOriginalContent()["id"];
    $instance = CategoryInstance::find($created_id);
    expect($instance->parameters->pluck('parameter_id'))->toEqual($this->category->parameters->pluck('id'));
    expect($instance->parameters->count() + $this->category->parameters->count())->toEqual(30 + 30);
    $this->post('/api/parameters', [
        'name' => 'Parameter name',
        'category_id' => $this->category->id,
    ]);
    $instance->refresh();
    $this->category->refresh();
    expect($instance->parameters->pluck('parameter_id'))->toEqual($this->category->parameters->pluck('id'));
    expect($instance->parameters->count() + $this->category->parameters->count())->toEqual(31 + 31);
});

test('fails when reading parameters', function () {
    $response = $this->get('/api/parameters/page/1');
    $response->assertStatus(403);
});

test('fails when picking non-existent parameter', function () {
    $response = $this->get('/api/parameters/-1');
    $response->assertStatus(404);
});

test('success when picking valid parameter', function () {
    $response = $this->get("/api/parameters/{$this->parameters->first()->id}");
    $response->assertStatus(200);
});

test('fails when editing parameter with empty name', function () {
    $response = $this->post("/api/parameters/{$this->parameters->first()->id}", [
        '_method' => 'patch',
        'name' => '',
    ]);
    $response->assertStatus(422);
});

test('success when editing parameter with valid parameters', function () {
    $response = $this->post("/api/parameters/{$this->parameters->first()->id}", [
        '_method' => 'patch',
        'name' => 'Parameter edited',
    ]);
    $response->assertStatus(200);
});

test('category id does not change even when category_id provided and edit is successful', function () {
    $newCategory = Category::factory()->create();
    $parameter = $this->parameters->first();
    expect($parameter->category_id)->toBe($this->category->id);
    $response = $this->post("/api/parameters/{$parameter->id}", [
        '_method' => 'patch',
        'name' => 'Parameter edited',
        'category_id' => $newCategory->id,
    ]);
    $parameter->refresh();
    expect($parameter->category_id)->toBe($this->category->id);
    $response->assertStatus(200);
});

test('fails when deleting non-existent parameter', function () {
    $response = $this->delete('/api/parameters/-1');
    $response->assertStatus(404);
});

test('fails when deleting a valid category with parameters', function () {
    $parameter_id = $this->parameters->first()->id;
    InstanceParameter::factory()->create(['parameter_id' => $parameter_id]);
    $response = $this->delete("/api/parameters/{$parameter_id}");
    $response->assertStatus(409);
});

test('success when deleting a valid parameter', function () {
    $response = $this->delete("/api/parameters/{$this->parameters->first()->id}");
    $response->assertStatus(200);
});

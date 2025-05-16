<?php

use App\Models\Category;
use App\Models\CategoryInstance;
use App\Models\InstanceParameter;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->category = Category::factory()->create();
    $this->project = Project::factory()->create();
    $this->actingAs(User::factory()->create());
    $this->instances = CategoryInstance::factory()->count(30)->create([
        'category_id' => $this->category->id,
        'project_id' => $this->project->id,
    ]);
});

test('fails when making instance without parameters', function () {
    $response = $this->post('/api/instances');
    $response->assertStatus(422);
});

test('fails when making instance without category_id parameter', function () {
    $response = $this->post('/api/instances', [
        'project_id' => $this->project->id,
    ]);
    $response->assertStatus(422);
});

test('fails when making instance without project_id parameter', function () {
    $response = $this->post('/api/instances', [
        'category_id' => $this->category->id,
    ]);
    $response->assertStatus(422);
});

test('fails when making instance with invalid category id', function () {
    $response = $this->post('/api/instances', [
        'category_id' => -1,
        'project_id' => $this->project->id,
    ]);
    $response->assertStatus(422);
});

test('fails when making instance with invalid project id', function () {
    $response = $this->post('/api/instances', [
        'category_id' => $this->category->id,
        'project_id' => -1,
    ]);
    $response->assertStatus(422);
});

test('success when making instance with valid parameters', function () {
    $response = $this->post('/api/instances', [
        'category_id' => $this->category->id,
        'project_id' => $this->project->id,
    ]);
    $response->assertStatus(201);
});

test('fails when reading instances', function () {
    $response = $this->get('/api/instances/page/1');
    $response->assertStatus(403);
});

test('fails when picking non-existent instance', function () {
    $response = $this->get('/api/instances/-1');
    $response->assertStatus(404);
});

test('success when picking valid instance', function () {
    $response = $this->get("/api/instances/{$this->instances->first()->id}");
    $response->assertStatus(200);
});

test('success when editing instance with valid parameters', function () {
    $response = $this->post("/api/instances/{$this->instances->first()->id}", [
        '_method' => 'patch',
        'summarisation' => 'This is a summary',
    ]);
    $response->assertStatus(200);
});

test('category and project id does not change even when both are provided and edit is successful', function () {
    $newCategory = Category::factory()->create();
    $newProject = Project::factory()->create();
    $instance = $this->instances->first();
    expect($instance->category_id)->toBe($this->category->id)
        ->and($instance->project_id)->toBe($this->project->id);
    $response = $this->post("/api/instances/{$instance->id}", [
        '_method' => 'patch',
        'summarisation' => 'This is a summary',
        'category_id' => $newCategory->id,
        'project_id' => $newProject->id,
    ]);
    $instance->refresh();
    expect($instance->category_id)->toBe($this->category->id)
        ->and($instance->project_id)->toBe($this->project->id);
    $response->assertStatus(200);
});

test('fails when deleting non-existent instance', function() {
    $response = $this->delete('/api/instances/-1');
    $response->assertStatus(404);
});

test('fails when deleting a valid instance with parameters', function() {
    $instance_id = $this->instances->first()->id;
    InstanceParameter::factory()->create(['instance_id' => $instance_id]);
    $response = $this->delete("/api/instances/{$instance_id}");
    $response->assertStatus(409);
});

test('success when deleting a valid parameter', function() {
    $response = $this->delete("/api/instances/{$this->instances->first()->id}");
    $response->assertStatus(200);
});

<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create();
    $this->actingAs($user);
});

test('fails when making project without params', function () {
    $response = $this->post('/api/projects');
    $response->assertStatus(422);
});

test('success when making project with name', function () {
    $response = $this->post('/api/projects', [
        'name' => fake()->sentence(3),
    ]);
    $response->assertStatus(201);
});

test('success when making project while including hidden_categories', function () {
    $response = $this->post('/api/projects', [
        'name' => fake()->sentence(3),
        'hidden_categories' => [1, 2, 3],
    ]);
    $response->assertStatus(201);
});

test('fails when editing non-existent project', function () {
    $response = $this->post('/api/projects/-1', [
        '_method' => 'patch',
    ]);
    $response->assertStatus(404);
});


test('fails when editing project with empty name', function () {
    $project = Project::factory()->create();
    $response = $this->post("/api/projects/{$project->id}", [
        '_method' => 'patch',
        'name' => '',
    ]);
    $response->assertStatus(422);
});

test('success when editing project with name', function () {
    $project = Project::factory()->create();
    $response = $this->post("/api/projects/{$project->id}", [
        '_method' => 'patch',
        'name' => 'edited',
    ]);
    $response->assertStatus(200);
});

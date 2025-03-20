<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->projects = Project::factory()->count(30)->create();
    $this->actingAs($this->user);
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

test('fails reading when no projects', function() {
    Project::query()->delete();
    $response = $this->get('/api/projects/page/1');
    $response->assertStatus(404);
});

test('success when reading all projects', function() {
    $response = $this->get('/api/projects/page/1');
    $response->assertStatus(200);
});

test('fails when picking non-existent project', function() {
    $response = $this->get('/api/projects/-1');
    $response->assertStatus(404);
});

test('success when picking a valid project', function() {
    $response = $this->get("/api/projects/{$this->projects->first()->id}");
    $response->assertStatus(200);
});

test('fails when editing non-existent project', function () {
    $response = $this->post('/api/projects/-1', [
        '_method' => 'patch',
    ]);
    $response->assertStatus(404);
});


test('fails when editing project with empty name', function () {
    $response = $this->post("/api/projects/{$this->projects->first()->id}", [
        '_method' => 'patch',
        'name' => '',
    ]);
    $response->assertStatus(422);
});

test('success when editing project with name', function () {
    $response = $this->post("/api/projects/{$this->projects->first()->id}", [
        '_method' => 'patch',
        'name' => 'edited',
    ]);
    $response->assertStatus(200);
});

test('success when editing project with hidden_categories', function () {
    $response = $this->post("/api/projects/{$this->projects->first()->id}", [
        '_method' => 'patch',
        'name' => 'edited',
        'hidden_categories' => [1, 2, 3],
    ]);
    $response->assertStatus(200);
});

test('fails when updating hidden category without parameters', function() {
    $response = $this->post("/api/projects/{$this->projects->first()->id}/hidden", [
        '_method' => 'put',
    ]);
    $response->assertStatus(422);
});

test('fails when updating hidden category with invalid action', function() {
    $response = $this->post("/api/projects/{$this->projects->first()->id}/hidden", [
        '_method' => 'put',
        'action' => 'flip',
        'hidden_categories' => [1, 2, 3],
    ]);
    $response->assertStatus(422);
});

test('fails when updating hidden category without action parameter', function() {
    $response = $this->post("/api/projects/{$this->projects->first()->id}/hidden", [
        '_method' => 'put',
        'hidden_categories' => [1, 2, 3],
    ]);
    $response->assertStatus(422);
});

test('fails when updating hidden category without hidden_categories parameter', function() {
    $response = $this->post("/api/projects/{$this->projects->first()->id}/hidden", [
        '_method' => 'put',
        'action' => 'add',
    ]);
    $response->assertStatus(422);
});

test('fails when updating hidden category with empty hidden_categories parameter', function() {
    $response = $this->post("/api/projects/{$this->projects->first()->id}/hidden", [
        '_method' => 'put',
        'action' => 'add',
        'hidden_categories' => [],
    ]);
    $response->assertStatus(422);
});

test('success when updating hidden category with valid parameters', function() {
    $project = $this->projects->first();
    expect($project->hidden_categories)->toEqual(null);
    $response = $this->put("/api/projects/{$project->id}/hidden", [
        'action' => 'add',
        'hidden_categories' => [1, 2, 3],
    ]);
    $response->assertStatus(200);
    $project->refresh();
    expect($project->hidden_categories)->toEqual([1, 2, 3]);

    $response = $this->put("/api/projects/{$project->id}/hidden", [
        'action' => 'remove',
        'hidden_categories' => [2, 3],
    ]);
    $response->assertStatus(200);
    $project->refresh();
    expect($project->hidden_categories)->toEqual([1]);
});

test('fails when adding user to project with non-existent project and user id', function() {
    $response = $this->put("/api/projects/addUser/-1/-1");
    $response->assertStatus(404);
});

test('fails when adding user to project with valid project but non-existent user id', function() {
    $response = $this->put("/api/projects/addUser/{$this->projects->first()->id}/-1");
    $response->assertStatus(404);
});

test('fails when adding user to project with non-existent project but valid user id', function() {
    $response = $this->put("/api/projects/addUser/-1/{$this->user->id}");
    $response->assertStatus(404);
});

test('fails when readding user to project', function() {
    $this->projects->first()->attachUser($this->user);
    $response = $this->put("/api/projects/addUser/{$this->projects->first()->id}/{$this->user->id}");
    $response->assertStatus(409);
});

test('success when adding user to project with valid project and user id', function() {
    $response = $this->put("/api/projects/addUser/{$this->projects->first()->id}/{$this->user->id}");
    $response->assertStatus(201);
});

test('fails when removing user from project with non-existent project and user id', function() {
    $response = $this->put("/api/projects/removeUser/-1/-1");
    $response->assertStatus(404);
});

test('fails when removing user from project with valid project but non-existent user id', function() {
    $response = $this->put("/api/projects/removeUser/{$this->projects->first()->id}/-1");
    $response->assertStatus(404);
});

test('fails when removing user from project with non-existent project but valid user id', function() {
    $this->projects->first()->attachUser($this->user);
    $response = $this->put("/api/projects/removeUser/-1/{$this->user->id}");
    $response->assertStatus(404);
});

test('fails when removing unrelated user from project', function() {
    $response = $this->put("/api/projects/removeUser/{$this->projects->first()->id}/{$this->user->id}");
    $response->assertStatus(404);
});

test('success when removing user from project with valid project and user id', function() {
    $this->projects->first()->attachUser($this->user);
    $response = $this->put("/api/projects/removeUser/{$this->projects->first()->id}/{$this->user->id}");
    $response->assertStatus(200);
});

test('fails when editing user role in project without parameters', function() {
    $this->projects->first()->attachUser($this->user);
    $response = $this->put("/api/projects/editRole/{$this->projects->first()->id}/{$this->user->id}");
    $response->assertStatus(422);
});

test('fails when editing user role in project with nonsense role parameter', function() {
    $this->projects->first()->attachUser($this->user);
    $response = $this->put("/api/projects/editRole/{$this->projects->first()->id}/{$this->user->id}", [
        'role' => 'timewarden',
    ]);
    $response->assertStatus(422);
});

test('fails when editing user role in project with non-existent project and user id', function() {
    $response = $this->put("/api/projects/editRole/-1/-1", [
        'role' => 'projectmanager',
    ]);
    $response->assertStatus(404);
});

test('fails when editing user role in project with valid project but non-existent user id', function() {
    $response = $this->put("/api/projects/editRole/{$this->projects->first()->id}/-1", [
        'role' => 'projectmanager',
    ]);
    $response->assertStatus(404);
});

test('fails when editing user role in project with non-existent project but valid user id', function() {
    $this->projects->first()->attachUser($this->user);
    $response = $this->put("/api/projects/editRole/-1/{$this->user->id}", [
        'role' => 'projectmanager',
    ]);
    $response->assertStatus(404);
});

test('fails when editing unrelated user role in project', function() {
    $response = $this->put("/api/projects/editRole/{$this->projects->first()->id}/{$this->user->id}", [
        'role' => 'projectmanager',
    ]);
    $response->assertStatus(404);
});

test('success when editing user role in project with valid project and user id', function() {
    $this->projects->first()->attachUser($this->user);
    $response = $this->put("/api/projects/editRole/{$this->projects->first()->id}/{$this->user->id}", [
        'role' => 'projectmanager',
    ]);
    $response->assertStatus(200);
});

test('fails when deleting non-existent project', function () {
    $response = $this->delete('/api/projects/-1');
    $response->assertStatus(404);
});

test('fails when deleting a valid project', function () {
    $response = $this->delete("/api/projects/{$this->projects->first()->id}");
    $response->assertStatus(200);
});

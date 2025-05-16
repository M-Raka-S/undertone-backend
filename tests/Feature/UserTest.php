<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->users = User::factory()->count(30)->create();
    $this->actingAs($this->users->first());
});

test('fails when making user', function () {
    $response = $this->post('/api/users');
    $response->assertStatus(403);
});

test('fails reading when no users', function () {
    User::query()->delete();
    $response = $this->get('/api/users/page/1');
    $response->assertStatus(404);
});

test('success when reading all users', function () {
    $response = $this->get('/api/users/page/1');
    $response->assertStatus(200);
    $response->assertJsonPath('data.*.username', $this->users->slice(0, 15)->pluck('username')->toArray());
    $response = $this->get('/api/users/page/2');
    $response->assertStatus(200);
    $response->assertJsonPath('data.*.username', $this->users->slice(15, 15)->pluck('username')->toArray());
});

test('fails when picking a non-existent user', function () {
    $response = $this->get('/api/users/-1');
    $response->assertStatus(404);
});

test('success when picking a valid user', function () {
    $response = $this->get("/api/users/{$this->users->first()->id}");
    $response->assertStatus(200);
    $response->assertJson([
        "id" => $this->users->first()->id,
        "username" => $this->users->first()->username,
    ]);
});

test('fails when editing non-existent user', function () {
    $response = $this->post("/api/users/-1", [
        '_method' => 'patch',
        'username' => 'edited',
    ]);
    $response->assertStatus(403);
});

test('fails when editing other user', function () {
    $response = $this->post("/api/users/{$this->users->get(1)->id}", [
        '_method' => 'patch',
        'username' => 'edited',
    ]);
    $response->assertStatus(403);
});

test('success when editing self', function () {
    $response = $this->post("/api/users/{$this->users->first()->id}", [
        '_method' => 'patch',
        'username' => 'edited',
    ]);
    $response->assertStatus(200);
});

test('success when editing self with same name', function () {
    $response = $this->post("/api/users/{$this->users->get(0)->id}", [
        '_method' => 'patch',
        'username' => $this->users->get(0)->username,
    ]);
    $response->assertStatus(200);
});

test('fails when deleting user', function () {
    $response = $this->delete("/api/users/{$this->users->first()->id}");
    $response->assertStatus(403);
});

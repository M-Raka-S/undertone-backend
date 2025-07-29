<?php

use App\Models\Category;
use App\Models\Parameter;
use App\Models\User;

beforeEach(function () {
    $this->categories = Category::factory()->count(30)->create();
    $this->actingAs(User::factory()->create());
});

test('fails when making category without parameters', function () {
    $response = $this->post('/api/categories');
    $response->assertStatus(422);
});

test('fails when making category with duplicate name', function () {
    $response = $this->post('/api/categories', [
        'name' => $this->categories->first()->name,
    ]);
    $response->assertStatus(422);
});

test('success when making category with valid parameters', function () {
    $response = $this->post('/api/categories', [
        'name' => "This is a unique name",
    ]);
    $response->assertStatus(201);
});

test('fails reading when no categories', function() {
    Category::query()->delete();
    $response = $this->get('/api/categories/page/1');
    $response->assertStatus(404);
});

test('success when reading categories', function() {
    $response = $this->get('/api/categories/page/1');
    $response->assertStatus(200);
});

test('fails when picking non-existent category', function() {
    $response = $this->get('/api/categories/-1');
    $response->assertStatus(404);
});

test('success when picking a valid category', function() {
    $response = $this->get("/api/categories/{$this->categories->first()->id}");
    $response->assertStatus(200);
});

test('fails when editing non-existent category', function() {
    $response = $this->post('/api/categories/-1', [
        '_method' => 'patch',
    ]);
    $response->assertStatus(404);
});

test('fails when editing a valid category with empty name', function() {
    $response = $this->post("/api/categories/{$this->categories->first()->id}", [
        '_method' => 'patch',
        'name' => '',
    ]);
    $response->assertStatus(422);
});

test('success when editing a valid category with valid parameters', function() {
    $response = $this->post("/api/categories/{$this->categories->first()->id}", [
        '_method' => 'patch',
        'name' => 'edited',
    ]);
    $response->assertStatus(200);
});

test('success when editing a valid category with valid parameters but same name', function() {
    $response = $this->post("/api/categories/{$this->categories->first()->id}", [
        '_method' => 'patch',
        'name' => $this->categories->first()->name,
    ]);
    $response->assertStatus(200);
});

test('fails when deleting non-existent category', function() {
    $response = $this->delete('/api/categories/-1');
    $response->assertStatus(404);
});

test('fails when deleting a valid category with parameters', function() {
    $category_id = $this->categories->first()->id;
    Parameter::factory()->create(['category_id' => $category_id]);
    $response = $this->delete("/api/categories/{$category_id}");
    $response->assertStatus(409);
});

test('success when deleting a valid category', function() {
    $response = $this->delete("/api/categories/{$this->categories->first()->id}");
    $response->assertStatus(200);
});

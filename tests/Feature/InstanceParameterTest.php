<?php

use App\Jobs\CopyParameterToInstances;
use App\Models\CategoryInstance;
use App\Models\InstanceParameter;
use App\Models\Parameter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->parameter = Parameter::factory()->create();
    $this->instance = CategoryInstance::factory()->create();
    $this->actingAs(User::factory()->create());
    $this->parameters = InstanceParameter::factory()->count(30)->create([
        'parameter_id' => $this->parameter->id,
        'instance_id' => $this->instance->id,
    ]);
});

test('fails when making instance parameter without parameters', function () {
    $response = $this->post('/api/instanceParameters');
    $response->assertStatus(422);
});

test('fails when making instance parameter without parameter_id parameter', function () {
    $response = $this->post('/api/instanceParameters', [
        'instance_id' => $this->instance->id,
    ]);
    $response->assertStatus(422);
});

test('fails when making instance parameter without instance_id parameter', function () {
    $response = $this->post('/api/instanceParameters', [
        'parameter_id' => $this->parameter->id,
    ]);
    $response->assertStatus(422);
});

test('fails when making instance parameter with invalid instance id', function () {
    $response = $this->post('/api/instanceParameters', [
        'instance_id' => -1,
        'parameter_id' => $this->parameter->id,
    ]);
    $response->assertStatus(422);
});

test('fails when making instance parameter with invalid parameter id', function () {
    $response = $this->post('/api/instanceParameters', [
        'instance_id' => $this->instance->id,
        'parameter_id' => -1,
    ]);
    $response->assertStatus(422);
});

test('success when making instance with valid parameters', function () {
    $response = $this->post('/api/instanceParameters', [
        'instance_id' => $this->instance->id,
        'parameter_id' => $this->parameter->id,
    ]);
    $response->assertStatus(201);
});

test('fails when reading instance parameters', function () {
    $response = $this->get('/api/instanceParameters/page/1');
    $response->assertStatus(403);
});

test('fails when picking non-existent instance parameter', function () {
    $response = $this->get('/api/instanceParameters/-1');
    $response->assertStatus(404);
});

test('success when picking valid instance parameter', function () {
    $response = $this->get("/api/instanceParameters/{$this->parameters->first()->id}");
    $response->assertStatus(200);
});

test('success when editing instance parameter with valid parameters', function () {
    $response = $this->post("/api/instanceParameters/{$this->parameters->first()->id}", [
        '_method' => 'patch',
        'value' => 'This is a value',
    ]);
    $response->assertStatus(200);
});

test('parameter and instance id does not change even when both are provided and edit is successful', function () {
    $newParameter = Parameter::factory()->create();
    $newInstance = CategoryInstance::factory()->create();
    $parameter = $this->parameters->first();
    expect($parameter->parameter_id)->toBe($this->parameter->id)
        ->and($parameter->instance_id)->toBe($this->instance->id);
    $response = $this->post("/api/instanceParameters/{$parameter->id}", [
        '_method' => 'patch',
        'summarisation' => 'This is a summary',
        'paramter_id' => $newParameter->id,
        'instance_id' => $newInstance->id,
    ]);
    $parameter->refresh();
    expect($parameter->parameter_id)->toBe($this->parameter->id)
        ->and($parameter->instance_id)->toBe($this->instance->id);
    $response->assertStatus(200);
});

test('fails when deleting non-existent instance parameter', function() {
    $response = $this->delete('/api/instanceParameters/-1');
    $response->assertStatus(404);
});

test('success when deleting a valid instance parameter', function() {
    $response = $this->delete("/api/instanceParameters/{$this->parameters->first()->id}");
    $response->assertStatus(200);
});

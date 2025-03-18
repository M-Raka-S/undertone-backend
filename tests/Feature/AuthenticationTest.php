<?php

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

class TestData {
    public static string $username;
    public static string $existing_username;
    public static string $existing_password;
    public static string $password;
    public static string $wrong_password;
    public static string $weak_password;
}
beforeAll(function() {
    TestData::$username = fake()->userName();
    TestData::$password = Str::password(16, true, true, false, false);;
    TestData::$wrong_password = TestData::$password . 'wrong';
    TestData::$weak_password = 'very weak password';
});
beforeEach(function () {
    $password = fake()->password();
    $user = User::factory()->create(['password' => $password]);
    TestData::$existing_username = $user->username;
    TestData::$existing_password = $password;
});

test('fails when register with no body', function() {
    $response = $this->post('/api/register');
    $response->assertStatus(422);
});

test('fails when register with only username', function() {
    $response = $this->post('/api/register', [
        'username' => TestData::$username,
    ]);
    $response->assertStatus(422);
});

test('fails when register with no password confirmation', function() {
    $response = $this->post('/api/register', [
        'username' => TestData::$username,
        'password' => TestData::$password,
    ]);
    $response->assertStatus(422);
});

test('fails when register with wrong password confirmation', function() {
    $response = $this->post('/api/register', [
        'username' => TestData::$username,
        'password' => TestData::$password,
        'password_confirmation' => TestData::$wrong_password,
    ]);
    $response->assertStatus(422);
});

test('fails when register with invalid password', function() {
    $response = $this->post('/api/register', [
        'username' => TestData::$username,
        'password' => TestData::$weak_password,
        'password_confirmation' => TestData::$weak_password,
    ]);
    $response->assertStatus(422);
});

test('fails when register with duplicate username', function() {
    $response = $this->post('/api/register', [
        'username' => TestData::$existing_username,
        'password' => TestData::$password,
        'password_confirmation' => TestData::$password,
    ]);
    $response->assertStatus(422);
});


test('success when register with correct body', function() {
    $response = $this->post('/api/register', [
        'username' => TestData::$username,
        'password' => TestData::$password,
        'password_confirmation' => TestData::$password,
    ]);
    $response->assertStatus(201);
});


test('fails when login with no body', function() {
    $response = $this->post('/api/login');
    $response->assertStatus(422);
});

test('fails when login with no password', function() {
    $response = $this->post('/api/login', [
        'username' => TestData::$existing_username,
    ]);
    $response->assertStatus(422);
});

test('fails when login with invalid credentials', function() {
    $response = $this->post('/api/login', [
        'username' => TestData::$existing_username,
        'password' => TestData::$password,
    ]);
    $response->assertStatus(401);
});

test('success when login with valid credentials', function() {
    $response = $this->post('/api/login', [
        'username' => TestData::$existing_username,
        'password' => TestData::$existing_password,
    ]);
    $response->assertStatus(200);
});

<?php

test('handle 404 not found', function () {
    $response = $this->get('/api/registerooni');

    $response
        ->assertStatus(404)
        ->assertJson([
            'message' => 'data not found.',
            'error' => 'the requested page does not exist.',
        ]);
});

test('handle 405 method not allowed', function () {
    $response = $this->get('/api/register');

    $response
        ->assertStatus(405)
        ->assertJson([
            'message' => 'method not allowed.',
            'error' => 'the requested HTTP method is not allowed for this route.',
        ]);
});

test('handle 500 internal server error', function () {
    $response = $this->get('/');

    $response
        ->assertStatus(500)
        ->assertJson([
            'message' => 'internal server error. ',
            'error' => 'a fatal error has occured.',
        ]);
});

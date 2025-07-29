<?php

use App\Models\Media;
use App\Models\Project;
use App\Models\User;

beforeEach(function () {
    $this->project = Project::factory()->create();
    $this->user = User::factory()->create();
    $this->project->attachUser($this->user, 'leadauthor');
    $this->media = Media::factory()->count(30)->create([
        'project_id' => $this->project->id,
    ]);
    $this->actingAs($this->user);
});

test('fails when showing media', function () {
    $response = $this->get('/api/media/page/1');
    $response->assertStatus(403);
});

test('fails when picking media', function () {
    $response = $this->get("/api/media/{$this->media->first()->id}");
    $response->assertStatus(403);
});

test('fails when editing media', function () {
    $response = $this->patch("/api/media/{$this->media->first()->id}");
    $response->assertStatus(403);
});

test('fails when deleting non-existent media', function () {
    $response = $this->delete("/api/media/-1");
    $response->assertStatus(404);
});

test('fails when deleting other\'s existing media', function () {
    $new_media = Media::factory()->create();
    $response = $this->delete("/api/media/{$new_media->id}");
    $response->assertStatus(403);
});

test('success when deleting owned existing media', function () {
    $response = $this->delete("/api/media/{$this->media->first()->id}");
    $response->assertStatus(200);
});

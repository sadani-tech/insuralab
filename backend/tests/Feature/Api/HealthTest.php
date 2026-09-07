<?php

it('returns a lightweight ok response with no infrastructure detail', function () {
    $response = $this->getJson('/api/v1/health');

    $response->assertOk()
        ->assertJson(['status' => 'ok'])
        ->assertJsonStructure(['status', 'service', 'api_version', 'time']);

    // Never leak environment / infrastructure specifics.
    $body = $response->json();
    expect($body)->not->toHaveKeys(['env', 'debug', 'database', 'php_version', 'app_key']);
});

<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('protects every new knowledge maintenance screen and renders them for editorial admins', function () {
    foreach (['knowledge-entries', 'knowledge-relations', 'source-passages'] as $slug) {
        $this->get('/admin/'.$slug)->assertRedirect();
    }
    $admin = User::factory()->create(['is_admin' => true]);
    foreach (['knowledge-entries', 'knowledge-relations', 'source-passages'] as $slug) {
        $this->actingAs($admin)->get('/admin/'.$slug)->assertOk();
    }
    $reader = User::factory()->create(['is_admin' => false]);
    foreach (['knowledge-entries', 'knowledge-relations', 'source-passages'] as $slug) {
        $this->actingAs($reader)->get('/admin/'.$slug)->assertForbidden();
    }
});

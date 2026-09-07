<?php

use App\Models\Assessment;
use App\Models\User;
use Database\Seeders\AssessmentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests away from the Filament admin panel', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

it('lets an admin user reach the panel', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)->get('/admin')->assertOk();
});

it('denies a non-admin user access to the panel', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)->get('/admin')->assertForbidden();
});

it('can access authorization is driven by the is_admin flag', function () {
    $panel = Filament\Facades\Filament::getPanel('admin');

    expect((new User(['is_admin' => true]))->canAccessPanel($panel))->toBeTrue()
        ->and((new User(['is_admin' => false]))->canAccessPanel($panel))->toBeFalse();
});

it('lets an admin edit assessment dimensions questions impacts and learning mappings', function () {
    $this->seed(AssessmentSeeder::class);
    $admin = User::factory()->create(['is_admin' => true]);
    $assessment = Assessment::where('slug', 'protection-needs')->firstOrFail();

    $this->actingAs($admin)
        ->get("/admin/assessments/{$assessment->id}/edit")
        ->assertOk()
        ->assertSee('Ambang relevansi')
        ->assertSee('Materi belajar umum');
});

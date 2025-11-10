<?php

namespace Tests\Feature\Api;

use Tests\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);
});

it('can audit palette', function () {
    $response = $this->postJson('/api/v1/a11y/audit-palette', [
        'colors' => ['#000000', '#FFFFFF'],
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'results' => [
            [
                'foreground' => '#000000',
                'background' => '#FFFFFF',
                'ratio' => 21,
                'is_accessible' => true,
            ],
            [
                'foreground' => '#FFFFFF',
                'background' => '#000000',
                'ratio' => 21,
                'is_accessible' => true,
            ],
        ],
    ]);
});

it('can generate text color', function () {
    $response = $this->postJson('/api/v1/a11y/generate-text-color', [
        'background_color' => '#000000',
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'text_color' => '#FFFFFF',
    ]);
});

it('can check contrast', function () {
    $response = $this->postJson('/api/v1/a11y/contrast-check', [
        'foreground' => '#000000',
        'background' => '#FFFFFF',
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'ratio' => 21,
        'is_accessible' => true,
    ]);
});

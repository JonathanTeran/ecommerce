<?php

use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the impersonation banner without crashing when the impersonated user is missing', function () {
    Filament::setCurrentPanel(Filament::getPanel('super-admin'));

    $response = $this
        ->withSession([
            app('impersonate')->getSessionKey() => 999999,
        ])
        ->get('/super-admin/login');

    $response->assertOk()
        ->assertDontSee('filament-impersonate::banner.impersonating', false);
});

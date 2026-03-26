<?php

use App\Filament\Resources\ActivityLogResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

beforeEach(function () {
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);
});

it('cannot create activity log records', function () {
    expect(ActivityLogResource::canCreate())->toBeFalse();
});

it('activity is logged when user is created', function () {
    $user = User::factory()->create();

    $activities = Activity::where('subject_type', User::class)
        ->where('subject_id', $user->id)
        ->get();

    expect($activities)->not->toBeEmpty();
});

it('has only list page', function () {
    $pages = ActivityLogResource::getPages();

    expect($pages)->toHaveKey('index')
        ->and($pages)->not->toHaveKey('create')
        ->and($pages)->not->toHaveKey('edit');
});

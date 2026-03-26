<?php

use App\Jobs\CheckSriAuthorizationStatus;
use App\Models\GeneralSetting;
use App\Models\Order;
use App\Services\SriService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\mock;

uses(RefreshDatabase::class);

it('schedules authorization check job', function () {
    GeneralSetting::create([]);

    $order = Order::factory()->create([
        'sri_access_key' => '2001202601120748180300110020010000000311234567814',
        'sri_authorization_status' => 'pending',
    ]);

    Queue::fake();

    $service = new SriService;
    $service->scheduleAuthorizationCheck($order);

    Queue::assertPushed(CheckSriAuthorizationStatus::class, function ($job) use ($order) {
        return $job->orderId === $order->id && $job->attempt === 1;
    });
});

it('updates order when authorized', function () {
    $order = Order::factory()->create([
        'sri_access_key' => '2001202601120748180300110020010000000311234567814',
        'sri_authorization_status' => 'pending',
    ]);

    $service = mock(SriService::class);
    $service->shouldReceive('authorize')
        ->once()
        ->andReturn([
            'status' => 'AUTORIZADO',
            'authorization_number' => '1234567890',
            'date' => '2026-01-23T12:10:00-05:00',
            'message' => 'OK',
        ]);

    Queue::fake();

    (new CheckSriAuthorizationStatus($order->id, 1))->handle($service);

    $order->refresh();

    expect($order->sri_authorization_status)->toBe('authorized');
    expect($order->sri_authorization_number)->toBe('1234567890');
    expect($order->sri_authorization_date)->not->toBeNull();
    expect($order->sri_error_message)->toBeNull();

    Queue::assertNothingPushed();
});

it('updates order when not authorized', function () {
    $order = Order::factory()->create([
        'sri_access_key' => '2001202601120748180300110020010000000311234567814',
        'sri_authorization_status' => 'pending',
    ]);

    $service = mock(SriService::class);
    $service->shouldReceive('authorize')
        ->once()
        ->andReturn([
            'status' => 'NO AUTORIZADO',
            'message' => 'ERROR',
        ]);

    Queue::fake();

    (new CheckSriAuthorizationStatus($order->id, 1))->handle($service);

    $order->refresh();

    expect($order->sri_authorization_status)->toBe('rejected');
    expect($order->sri_error_message)->toBe('SRI: ERROR');

    Queue::assertNothingPushed();
});

it('retries authorization when pending', function () {
    $order = Order::factory()->create([
        'sri_access_key' => '2001202601120748180300110020010000000311234567814',
        'sri_authorization_status' => 'pending',
    ]);

    $service = mock(SriService::class);
    $service->shouldReceive('authorize')
        ->once()
        ->andReturn([
            'status' => 'PENDING',
            'message' => 'No se encontró autorización',
        ]);

    Queue::fake();

    (new CheckSriAuthorizationStatus($order->id, 1))->handle($service);

    $order->refresh();

    expect($order->sri_authorization_status)->toBe('pending');
    expect($order->sri_error_message)->toBe('SRI: No se encontró autorización');

    Queue::assertPushed(CheckSriAuthorizationStatus::class, function ($job) use ($order) {
        return $job->orderId === $order->id && $job->attempt === 2;
    });
});

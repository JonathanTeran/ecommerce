<?php

use App\Support\ViteAssets;
use Illuminate\Support\Str;

beforeEach(function () {
    ViteAssets::resetCache();
});

it('falls back to built assets when the hot file is missing', function () {
    $hotFile = storage_path('framework/'.Str::uuid().'.hot');

    $html = ViteAssets::tags(['resources/css/app.css', 'resources/js/app.js'], $hotFile)->toHtml();

    expect($html)
        ->toContain('/build/assets/')
        ->toContain('<link rel="stylesheet"')
        ->toContain('<script type="module"')
        ->not->toContain('@vite/client');
});

it('falls back to built assets when the hot server is unreachable', function () {
    $hotFile = storage_path('framework/'.Str::uuid().'.hot');

    file_put_contents($hotFile, 'http://127.0.0.1:65534');

    $html = ViteAssets::tags(['resources/css/app.css', 'resources/js/app.js'], $hotFile)->toHtml();

    expect($html)
        ->toContain('/build/assets/')
        ->not->toContain('@vite/client');

    unlink($hotFile);
});

it('uses the hot server when it is reachable', function () {
    $server = stream_socket_server('tcp://127.0.0.1:0', $errorNumber, $errorMessage);

    expect($server)->not->toBeFalse();

    $address = stream_socket_get_name($server, false);
    $port = (int) str($address)->afterLast(':')->toString();
    $hotUrl = "http://127.0.0.1:{$port}";
    $hotFile = storage_path('framework/'.Str::uuid().'.hot');

    file_put_contents($hotFile, $hotUrl);

    $html = ViteAssets::tags(['resources/js/app.js'], $hotFile)->toHtml();

    expect($html)
        ->toContain("{$hotUrl}/@vite/client")
        ->toContain("{$hotUrl}/resources/js/app.js");

    fclose($server);
    unlink($hotFile);
});

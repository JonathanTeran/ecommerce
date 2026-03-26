<?php

use App\Support\SectionStyleHelper;

it('returns empty string for empty style array', function () {
    expect(SectionStyleHelper::toInlineCss([]))->toBe('');
});

it('returns empty string when all values are null or default', function () {
    $style = SectionStyleHelper::defaultStyle();

    expect(SectionStyleHelper::toInlineCss($style))->toContain('padding-top: 3rem');
});

it('generates background-color for solid bg_color', function () {
    $style = ['bg_color' => '#1e3a8a'];

    $css = SectionStyleHelper::toInlineCss($style);

    expect($css)->toContain('background-color: #1e3a8a');
});

it('generates linear-gradient for gradient type', function () {
    $style = [
        'bg_type' => 'gradient',
        'bg_gradient_from' => '#000000',
        'bg_gradient_to' => '#ffffff',
        'bg_gradient_direction' => 'to-r',
    ];

    $css = SectionStyleHelper::toInlineCss($style);

    expect($css)->toContain('background: linear-gradient(to right, #000000, #ffffff)');
});

it('generates correct padding for each preset', function (string $preset, string $expected) {
    $style = ['padding_preset' => $preset];

    $css = SectionStyleHelper::toInlineCss($style);

    expect($css)->toContain("padding-top: {$expected}")
        ->toContain("padding-bottom: {$expected}");
})->with([
    'compact' => ['compact', '1rem'],
    'normal' => ['normal', '3rem'],
    'spacious' => ['spacious', '5rem'],
    'extra' => ['extra', '8rem'],
]);

it('generates border-radius for each preset', function (string $preset, string $expected) {
    $style = ['border_radius' => $preset];

    $css = SectionStyleHelper::toInlineCss($style);

    expect($css)->toContain("border-radius: {$expected}");
})->with([
    'small' => ['small', '0.5rem'],
    'medium' => ['medium', '1rem'],
    'large' => ['large', '1.5rem'],
    'full' => ['full', '3rem'],
]);

it('does not generate border-radius for none preset', function () {
    $style = ['border_radius' => 'none'];

    $css = SectionStyleHelper::toInlineCss($style);

    expect($css)->not->toContain('border-radius');
});

it('generates border-top and border-bottom with custom color', function () {
    $style = [
        'border_top' => true,
        'border_bottom' => true,
        'border_color' => '#ff0000',
    ];

    $css = SectionStyleHelper::toInlineCss($style);

    expect($css)->toContain('border-top: 1px solid #ff0000')
        ->toContain('border-bottom: 1px solid #ff0000');
});

it('uses default border color when no custom color set', function () {
    $style = ['border_top' => true];

    $css = SectionStyleHelper::toInlineCss($style);

    expect($css)->toContain('border-top: 1px solid #e5e7eb');
});

it('generates font-family for custom font', function () {
    $style = ['font_family' => 'Poppins'];

    $css = SectionStyleHelper::toInlineCss($style);

    expect($css)->toContain("font-family: 'Poppins', sans-serif");
});

it('combines multiple properties in one inline string', function () {
    $style = [
        'bg_color' => '#1e3a8a',
        'text_color' => '#ffffff',
        'padding_preset' => 'spacious',
        'border_radius' => 'medium',
        'font_family' => 'Inter',
    ];

    $css = SectionStyleHelper::toInlineCss($style);

    expect($css)->toContain('background-color: #1e3a8a')
        ->toContain('color: #ffffff')
        ->toContain('padding-top: 5rem')
        ->toContain('border-radius: 1rem')
        ->toContain("font-family: 'Inter', sans-serif");
});

it('builds correct google fonts url for multiple fonts', function () {
    $url = SectionStyleHelper::googleFontsUrl(['Inter', 'Poppins']);

    expect($url)->toContain('fonts.googleapis.com/css2')
        ->toContain('family=Inter')
        ->toContain('family=Poppins')
        ->toContain('display=swap');
});

it('returns null for empty font list', function () {
    expect(SectionStyleHelper::googleFontsUrl([]))->toBeNull();
    expect(SectionStyleHelper::googleFontsUrl([null, null]))->toBeNull();
});

it('deduplicates fonts in google fonts url', function () {
    $url = SectionStyleHelper::googleFontsUrl(['Inter', 'Inter', 'Poppins']);

    expect(substr_count($url, 'family=Inter'))->toBe(1);
});

it('prefers gradient over solid when both provided', function () {
    $style = [
        'bg_type' => 'gradient',
        'bg_color' => '#ff0000',
        'bg_gradient_from' => '#000000',
        'bg_gradient_to' => '#ffffff',
        'bg_gradient_direction' => 'to-b',
    ];

    $css = SectionStyleHelper::toInlineCss($style);

    expect($css)->toContain('linear-gradient')
        ->not->toContain('background-color');
});

it('falls back to solid when gradient colors are missing', function () {
    $style = [
        'bg_type' => 'gradient',
        'bg_color' => '#ff0000',
        'bg_gradient_from' => null,
        'bg_gradient_to' => null,
    ];

    $css = SectionStyleHelper::toInlineCss($style);

    expect($css)->toContain('background-color: #ff0000')
        ->not->toContain('linear-gradient');
});

it('provides default style array with all expected keys', function () {
    $style = SectionStyleHelper::defaultStyle();

    expect($style)->toHaveKeys([
        'bg_type', 'bg_color', 'bg_gradient_from', 'bg_gradient_to',
        'bg_gradient_direction', 'text_color', 'padding_preset',
        'border_radius', 'border_top', 'border_bottom',
        'border_color', 'font_family',
    ]);
});

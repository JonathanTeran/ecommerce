<?php

use App\Enums\SectionType;

it('has a non-empty label for every case', function (SectionType $type) {
    expect($type->label())->toBeString()->not->toBeEmpty();
})->with(SectionType::cases());

it('returns correct blade component name for each case', function () {
    expect(SectionType::Hero->bladeComponent())->toBe('sections.hero');
    expect(SectionType::CategoryStrip->bladeComponent())->toBe('sections.category-strip');
    expect(SectionType::PromoBanners->bladeComponent())->toBe('sections.promo-banners');
    expect(SectionType::CtaStrip->bladeComponent())->toBe('sections.cta-strip');
    expect(SectionType::ProductGrid->bladeComponent())->toBe('sections.product-grid');
    expect(SectionType::BrandSlider->bladeComponent())->toBe('sections.brand-slider');
    expect(SectionType::ValueProps->bladeComponent())->toBe('sections.value-props');
});

it('returns non-empty default config for every case', function (SectionType $type) {
    expect($type->defaultConfig())->toBeArray()->not->toBeEmpty();
})->with(SectionType::cases());

it('hero default config has required keys', function () {
    $config = SectionType::Hero->defaultConfig();

    expect($config)->toHaveKeys([
        'heading', 'subheading', 'badge_text',
        'cta_text', 'cta_url',
        'secondary_cta_text', 'secondary_cta_url',
        'background_image',
    ]);
});

it('product grid default config has correct defaults', function () {
    $config = SectionType::ProductGrid->defaultConfig();

    expect($config['source'])->toBe('trending')
        ->and($config['limit'])->toBe(8)
        ->and($config['enable_infinite_scroll'])->toBeTrue()
        ->and($config['category_id'])->toBeNull();
});

it('value props default config has four items', function () {
    $config = SectionType::ValueProps->defaultConfig();

    expect($config['items'])->toBeArray()->toHaveCount(4);

    foreach ($config['items'] as $item) {
        expect($item)->toHaveKeys(['icon', 'title', 'description']);
    }
});

it('promo banners default config has two banners', function () {
    $config = SectionType::PromoBanners->defaultConfig();

    expect($config['banners'])->toBeArray()->toHaveCount(2);

    foreach ($config['banners'] as $banner) {
        expect($banner)->toHaveKeys(['title', 'subtitle', 'badge_text', 'badge_color', 'button_text', 'button_url', 'image']);
    }
});

it('category strip default config has empty categories array', function () {
    $config = SectionType::CategoryStrip->defaultConfig();

    expect($config['categories'])->toBeArray()->toBeEmpty();
});

it('brand slider default config has heading and subheading', function () {
    $config = SectionType::BrandSlider->defaultConfig();

    expect($config)->toHaveKeys(['heading', 'subheading']);
    expect($config['heading'])->not->toBeEmpty();
    expect($config['subheading'])->not->toBeEmpty();
});

it('cta strip default config has all required keys', function () {
    $config = SectionType::CtaStrip->defaultConfig();

    expect($config)->toHaveKeys(['heading', 'description', 'button_text', 'button_url', 'icon']);
    expect($config['icon'])->toBe('puzzle');
});

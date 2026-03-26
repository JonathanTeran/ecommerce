<?php

use App\Enums\PageBlockType;

it('has correct labels for all cases', function () {
    expect(PageBlockType::RichText->label())->toBe('Texto Enriquecido');
    expect(PageBlockType::HeroBanner->label())->toBe('Banner Hero');
    expect(PageBlockType::ImageWithCaption->label())->toBe('Imagen con Titulo');
    expect(PageBlockType::FeaturesGrid->label())->toBe('Grilla de Caracteristicas');
    expect(PageBlockType::Faq->label())->toBe('Preguntas Frecuentes');
    expect(PageBlockType::CallToAction->label())->toBe('Llamada a la Accion');
    expect(PageBlockType::Spacer->label())->toBe('Separador');
    expect(PageBlockType::Gallery->label())->toBe('Galeria de Imagenes');
    expect(PageBlockType::Testimonials->label())->toBe('Testimonios');
});

it('generates correct blade component paths', function () {
    expect(PageBlockType::RichText->bladeComponent())->toBe('page-blocks.rich-text');
    expect(PageBlockType::HeroBanner->bladeComponent())->toBe('page-blocks.hero-banner');
    expect(PageBlockType::ImageWithCaption->bladeComponent())->toBe('page-blocks.image-with-caption');
    expect(PageBlockType::FeaturesGrid->bladeComponent())->toBe('page-blocks.features-grid');
    expect(PageBlockType::Faq->bladeComponent())->toBe('page-blocks.faq');
    expect(PageBlockType::CallToAction->bladeComponent())->toBe('page-blocks.call-to-action');
    expect(PageBlockType::Spacer->bladeComponent())->toBe('page-blocks.spacer');
    expect(PageBlockType::Gallery->bladeComponent())->toBe('page-blocks.gallery');
    expect(PageBlockType::Testimonials->bladeComponent())->toBe('page-blocks.testimonials');
});

it('has correct icon for all cases', function () {
    foreach (PageBlockType::cases() as $case) {
        expect($case->icon())->toStartWith('heroicon-o-');
    }
});

it('has exactly 9 block types', function () {
    expect(PageBlockType::cases())->toHaveCount(9);
});

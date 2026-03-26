<?php

namespace App\Enums;

enum PageBlockType: string
{
    case RichText = 'rich_text';
    case HeroBanner = 'hero_banner';
    case ImageWithCaption = 'image_with_caption';
    case FeaturesGrid = 'features_grid';
    case Faq = 'faq';
    case CallToAction = 'call_to_action';
    case Spacer = 'spacer';
    case Gallery = 'gallery';
    case Testimonials = 'testimonials';

    public function label(): string
    {
        return match ($this) {
            self::RichText => 'Texto Enriquecido',
            self::HeroBanner => 'Banner Hero',
            self::ImageWithCaption => 'Imagen con Titulo',
            self::FeaturesGrid => 'Grilla de Caracteristicas',
            self::Faq => 'Preguntas Frecuentes',
            self::CallToAction => 'Llamada a la Accion',
            self::Spacer => 'Separador',
            self::Gallery => 'Galeria de Imagenes',
            self::Testimonials => 'Testimonios',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::RichText => 'heroicon-o-document-text',
            self::HeroBanner => 'heroicon-o-photo',
            self::ImageWithCaption => 'heroicon-o-camera',
            self::FeaturesGrid => 'heroicon-o-squares-2x2',
            self::Faq => 'heroicon-o-question-mark-circle',
            self::CallToAction => 'heroicon-o-megaphone',
            self::Spacer => 'heroicon-o-minus',
            self::Gallery => 'heroicon-o-photo',
            self::Testimonials => 'heroicon-o-chat-bubble-left-right',
        };
    }

    public function bladeComponent(): string
    {
        return 'page-blocks.' . str_replace('_', '-', $this->value);
    }
}

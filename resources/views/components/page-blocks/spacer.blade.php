@props(['data' => []])
@php
    $height = $data['height'] ?? 'medium';
    $heightClass = match($height) {
        'small' => 'h-8',
        'medium' => 'h-16',
        'large' => 'h-24',
        'extra' => 'h-32',
        default => 'h-16',
    };
@endphp

<div class="{{ $heightClass }}"></div>

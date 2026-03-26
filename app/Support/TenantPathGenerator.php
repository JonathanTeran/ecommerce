<?php

namespace App\Support;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;

class TenantPathGenerator extends DefaultPathGenerator
{
    public function getPath(Media $media): string
    {
        $tenantId = $media->model?->tenant_id ?? 'shared';

        return "tenant-{$tenantId}/" . $media->id . '/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media) . 'conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media) . 'responsive/';
    }
}

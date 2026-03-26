<?php

use App\Filament\Exports\ProductExporter;
use App\Filament\Imports\ProductImporter;
use App\Models\Product;
use App\Models\Tenant;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    app()->instance('current_tenant', $this->tenant);
});

it('has product exporter with correct columns', function () {
    $columns = ProductExporter::getColumns();

    $columnNames = collect($columns)->map(fn ($col) => $col->getName())->toArray();

    expect($columnNames)->toContain('sku')
        ->and($columnNames)->toContain('name')
        ->and($columnNames)->toContain('price')
        ->and($columnNames)->toContain('quantity')
        ->and($columnNames)->toContain('category.name')
        ->and($columnNames)->toContain('brand.name')
        ->and($columnNames)->toContain('compare_price')
        ->and($columnNames)->toContain('cost')
        ->and($columnNames)->toContain('is_active')
        ->and($columnNames)->toContain('is_featured')
        ->and($columnNames)->toContain('is_new')
        ->and($columnNames)->toContain('weight')
        ->and($columnNames)->toContain('warranty_months')
        ->and($columnNames)->toContain('views_count')
        ->and($columnNames)->toContain('sales_count')
        ->and($columnNames)->toContain('created_at');
});

it('has product importer with enhanced columns', function () {
    $columns = ProductImporter::getColumns();

    $columnNames = collect($columns)->map(fn ($col) => $col->getName())->toArray();

    expect($columnNames)->toContain('sku')
        ->and($columnNames)->toContain('name')
        ->and($columnNames)->toContain('price')
        ->and($columnNames)->toContain('compare_price')
        ->and($columnNames)->toContain('cost')
        ->and($columnNames)->toContain('short_description')
        ->and($columnNames)->toContain('low_stock_threshold')
        ->and($columnNames)->toContain('is_featured')
        ->and($columnNames)->toContain('is_new')
        ->and($columnNames)->toContain('weight')
        ->and($columnNames)->toContain('warranty_months');
});

it('exporter targets the product model', function () {
    $reflection = new ReflectionClass(ProductExporter::class);
    $property = $reflection->getProperty('model');

    expect($property->getValue())->toBe(Product::class);
});

it('importer targets the product model', function () {
    $reflection = new ReflectionClass(ProductImporter::class);
    $property = $reflection->getProperty('model');

    expect($property->getValue())->toBe(Product::class);
});

it('exporter has all spanish labels', function () {
    $columns = ProductExporter::getColumns();

    foreach ($columns as $column) {
        expect($column->getLabel())->not->toBeNull();
    }
});

it('importer has all spanish labels', function () {
    $columns = ProductImporter::getColumns();

    foreach ($columns as $column) {
        expect($column->getLabel())->not->toBeNull();
    }
});

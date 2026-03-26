<?php

use App\Enums\Module;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    app()->instance('current_tenant', $this->tenant);
});

it('creates attribute with tenant isolation', function () {
    $attribute = Attribute::factory()->create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Material',
        'type' => 'string',
    ]);

    expect($attribute->name)->toBe('Material');
    expect($attribute->tenant_id)->toBe($this->tenant->id);
});

it('creates select attribute with options', function () {
    $attribute = Attribute::factory()->select(['Aluminio', 'Acero', 'Plástico'])->create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Material',
    ]);

    expect($attribute->type)->toBe('select');
    expect($attribute->options)->toContain('Aluminio');
});

it('auto-generates slug from name', function () {
    $attribute = Attribute::factory()->create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Tamaño de Pantalla',
    ]);

    expect($attribute->slug)->toBe('tamano-de-pantalla');
});

it('assigns attribute value to product', function () {
    $attribute = Attribute::factory()->create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Voltaje',
        'type' => 'string',
    ]);

    $product = Product::factory()->create(['tenant_id' => $this->tenant->id]);

    $value = AttributeValue::create([
        'attribute_id' => $attribute->id,
        'product_id' => $product->id,
        'value' => '220V',
    ]);

    expect($value->value)->toBe('220V');
    expect($product->attributeValues)->toHaveCount(1);
});

it('enforces unique attribute-product combination', function () {
    $attribute = Attribute::factory()->create(['tenant_id' => $this->tenant->id]);
    $product = Product::factory()->create(['tenant_id' => $this->tenant->id]);

    AttributeValue::create([
        'attribute_id' => $attribute->id,
        'product_id' => $product->id,
        'value' => 'Value 1',
    ]);

    expect(fn () => AttributeValue::create([
        'attribute_id' => $attribute->id,
        'product_id' => $product->id,
        'value' => 'Value 2',
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

it('cascades delete when attribute is removed', function () {
    $attribute = Attribute::factory()->create(['tenant_id' => $this->tenant->id]);
    $product = Product::factory()->create(['tenant_id' => $this->tenant->id]);

    AttributeValue::create([
        'attribute_id' => $attribute->id,
        'product_id' => $product->id,
        'value' => 'Test',
    ]);

    expect(AttributeValue::count())->toBe(1);

    $attribute->delete();

    expect(AttributeValue::count())->toBe(0);
});

it('cascades delete when product is removed', function () {
    $attribute = Attribute::factory()->create(['tenant_id' => $this->tenant->id]);
    $product = Product::factory()->create(['tenant_id' => $this->tenant->id]);

    AttributeValue::create([
        'attribute_id' => $attribute->id,
        'product_id' => $product->id,
        'value' => 'Test',
    ]);

    expect(AttributeValue::count())->toBe(1);

    $product->forceDelete();

    expect(AttributeValue::count())->toBe(0);
});

it('isolates attributes by tenant', function () {
    Attribute::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Attr1']);

    $otherTenant = Tenant::factory()->create();
    Attribute::withoutGlobalScopes()->create([
        'tenant_id' => $otherTenant->id,
        'name' => 'Attr2',
        'slug' => 'attr2',
        'type' => 'string',
    ]);

    expect(Attribute::count())->toBe(1);
    expect(Attribute::first()->name)->toBe('Attr1');
});

it('has custom attributes module enum', function () {
    $module = Module::CustomAttributes;

    expect($module->value)->toBe('custom_attributes');
    expect($module->label())->toBe('Atributos Personalizados');
});

it('retrieves attribute values through product relationship', function () {
    $attr1 = Attribute::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Color']);
    $attr2 = Attribute::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Talla']);

    $product = Product::factory()->create(['tenant_id' => $this->tenant->id]);

    AttributeValue::create([
        'attribute_id' => $attr1->id,
        'product_id' => $product->id,
        'value' => 'Rojo',
    ]);

    AttributeValue::create([
        'attribute_id' => $attr2->id,
        'product_id' => $product->id,
        'value' => 'XL',
    ]);

    $product->refresh();

    expect($product->attributeValues)->toHaveCount(2);
    expect($product->attributeValues->pluck('value')->toArray())->toContain('Rojo', 'XL');
});

it('counts values through attribute relationship', function () {
    $attribute = Attribute::factory()->create(['tenant_id' => $this->tenant->id]);

    $products = Product::factory()->count(3)->create(['tenant_id' => $this->tenant->id]);

    foreach ($products as $product) {
        AttributeValue::create([
            'attribute_id' => $attribute->id,
            'product_id' => $product->id,
            'value' => 'val-'.$product->id,
        ]);
    }

    expect($attribute->values()->count())->toBe(3);
});

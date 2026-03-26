<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class MacBookVariantsSeeder extends Seeder
{
    public function run()
    {
        $product = Product::where('name', 'like', '%MacBook Pro 16%')->first();

        if (! $product) {
            $this->command->error('MacBook Pro not found!');

            return;
        }

        $this->command->info("Adding variants to: {$product->name}");

        // Clear existing variants to avoid duplicates
        $product->variants()->delete();

        $variants = [
            [
                'name' => 'Ultimate Edition (128GB RAM / 8TB SSD)',
                'sku' => $product->sku.'-ULT',
                'price' => $product->price + 3200,
                'quantity' => 5,
                'options' => [
                    'Memoria RAM' => '128 GB Unificada',
                    'Almacenamiento' => '8 TB SSD',
                    'Chip' => 'M3 Max (16-core CPU, 40-core GPU)',
                ],
            ],
            [
                'name' => 'Developer Kit (96GB RAM / 4TB SSD)',
                'sku' => $product->sku.'-DEV',
                'price' => $product->price + 1400,
                'quantity' => 10,
                'options' => [
                    'Memoria RAM' => '96 GB Unificada',
                    'Almacenamiento' => '4 TB SSD',
                    'Chip' => 'M3 Max (14-core CPU, 30-core GPU)',
                ],
            ],
            [
                'name' => 'Creator Kit (48GB RAM / 1TB SSD)',
                'sku' => $product->sku.'-CRE',
                'price' => $product->price + 400,
                'quantity' => 25,
                'options' => [
                    'Memoria RAM' => '48 GB Unificada',
                    'Almacenamiento' => '1 TB SSD',
                    'Chip' => 'M3 Max (14-core CPU, 30-core GPU)',
                ],
            ],
        ];

        foreach ($variants as $data) {
            $product->variants()->create($data);
            $this->command->info("Created: {$data['name']}");
        }
    }
}

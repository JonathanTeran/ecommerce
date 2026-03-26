<?php

namespace Database\Seeders;

use App\Enums\Module;
use App\Enums\PlanType;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PlanType::cases() as $type) {
            Plan::updateOrCreate(
                ['type' => $type->value],
                [
                    'name' => $type->label(),
                    'slug' => $type->value,
                    'price' => $type->price(),
                    'billing_period' => 'monthly',
                    'max_products' => $type->maxProducts(),
                    'max_users' => $type->maxUsers(),
                    'modules' => array_map(fn (Module $m) => $m->value, $type->modules()),
                    'is_active' => true,
                    'sort_order' => match ($type) {
                        PlanType::Basic => 1,
                        PlanType::Professional => 2,
                        PlanType::Enterprise => 3,
                    },
                ]
            );
        }
    }
}

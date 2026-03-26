<?php

namespace App\Services;

use App\Models\GeneralSetting;
use Illuminate\Support\Collection;

class ShippingService
{
    /**
     * Get available shipping options for a given order amount and optional weight/zone.
     *
     * @return Collection<int, object>
     */
    public function getAvailableRates(float $orderAmount, ?float $weight = null, ?string $zone = null): Collection
    {
        $settings = GeneralSetting::cached();

        if (! $settings) {
            return collect();
        }

        return $settings->getAvailableShippingRates($orderAmount, $weight, $zone)
            ->map(fn (object $rate) => [
                'key' => $rate->key,
                'carrier_name' => $rate->carrier_name,
                'carrier_logo' => $rate->carrier_logo,
                'rate_name' => $rate->rate_name,
                'price' => $rate->price,
                'estimated_days' => $rate->estimated_days,
                'zone' => $rate->zone,
            ]);
    }

    /**
     * Get the price for a specific shipping rate by key.
     */
    public function getRatePrice(string $rateKey): float
    {
        $settings = GeneralSetting::cached();

        if (! $settings) {
            return 0.00;
        }

        $rate = $settings->getAvailableShippingRates()->firstWhere('key', $rateKey);

        return $rate ? $rate->price : 0.00;
    }

    /**
     * Check if free shipping is available (any rate with price 0).
     */
    public function hasFreeShipping(float $orderAmount): bool
    {
        $settings = GeneralSetting::cached();

        if (! $settings) {
            return false;
        }

        return $settings->getAvailableShippingRates($orderAmount)
            ->contains(fn (object $rate) => $rate->price == 0);
    }
}

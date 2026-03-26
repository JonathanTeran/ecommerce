<?php

namespace App\Services\Sri;

use App\Models\GeneralSetting;
use App\Models\Order;

class SriAccessKeyGenerator
{
    public function __construct(
        protected string $ruc,
        protected string $environment,
        protected string $codDoc,
        protected string $estab,
        protected string $ptoEmi,
    ) {}

    /**
     * Generate Access Key (Clave de Acceso)
     */
    public function generate(Order $order): string
    {
        $date = $order->created_at->format('dmY');

        // Secuencial from GeneralSetting with pessimistic lock
        $settings = GeneralSetting::lockForUpdate()
            ->firstOrCreate([], ['site_name' => 'Tienda Virtual', 'sri_next_sequence' => 1]);
        $sequence = $settings->sri_next_sequence ?? 1;
        $sequential = str_pad($sequence, 9, '0', STR_PAD_LEFT);
        $settings->increment('sri_next_sequence');

        $numericCode = str_pad((string) random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT);
        $emissionType = '1'; // Normal

        $key = $date.$this->codDoc.$this->ruc.$this->environment.$this->estab.$this->ptoEmi.$sequential.$numericCode.$emissionType;

        // Modulo 11 Check Digit
        $checkDigit = $this->generateCheckDigit($key);

        return $key.$checkDigit;
    }

    protected function generateCheckDigit(string $key): int
    {
        $factors = [2, 3, 4, 5, 6, 7];
        $sum = 0;
        $factorIndex = 0;

        for ($i = strlen($key) - 1; $i >= 0; $i--) {
            $sum += $key[$i] * $factors[$factorIndex];
            $factorIndex = ($factorIndex + 1) % 6;
        }

        $residue = $sum % 11;
        $digit = 11 - $residue;

        if ($digit == 11) {
            return 0;
        }
        if ($digit == 10) {
            return 1;
        }

        return $digit;
    }
}

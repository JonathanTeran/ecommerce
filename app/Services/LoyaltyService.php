<?php

namespace App\Services;

use App\Models\LoyaltyProgram;
use App\Models\LoyaltyTransaction;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LoyaltyService
{
    public function isActive(): bool
    {
        $program = LoyaltyProgram::forCurrentTenant();

        return $program?->is_active ?? false;
    }

    public function getProgram(): ?LoyaltyProgram
    {
        return LoyaltyProgram::forCurrentTenant();
    }

    public function awardPoints(User $user, Order $order): ?LoyaltyTransaction
    {
        $program = $this->getProgram();
        if (! $program || ! $program->is_active) {
            return null;
        }

        $points = $this->calculatePointsForOrder($order);
        if ($points <= 0) {
            return null;
        }

        return DB::transaction(function () use ($user, $order, $points) {
            $newBalance = $user->points_balance + $points;
            $user->update(['points_balance' => $newBalance]);

            return LoyaltyTransaction::create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'points' => $points,
                'type' => 'earned',
                'description' => "Puntos ganados por orden #{$order->order_number}",
                'balance_after' => $newBalance,
            ]);
        });
    }

    public function redeemPoints(User $user, int $points, ?Order $order = null): ?LoyaltyTransaction
    {
        $program = $this->getProgram();
        if (! $program || ! $program->is_active) {
            return null;
        }

        if ($points < $program->minimum_redemption_points) {
            return null;
        }

        if ($user->points_balance < $points) {
            return null;
        }

        return DB::transaction(function () use ($user, $points, $order) {
            $newBalance = $user->points_balance - $points;
            $user->update(['points_balance' => $newBalance]);

            return LoyaltyTransaction::create([
                'user_id' => $user->id,
                'order_id' => $order?->id,
                'points' => -$points,
                'type' => 'redeemed',
                'description' => $order
                    ? "Puntos canjeados en orden #{$order->order_number}"
                    : 'Puntos canjeados',
                'balance_after' => $newBalance,
            ]);
        });
    }

    public function calculatePointsForOrder(Order $order): int
    {
        $program = $this->getProgram();
        if (! $program) {
            return 0;
        }

        return (int) floor((float) $order->subtotal * $program->points_per_dollar);
    }

    public function calculateRedemptionValue(int $points): float
    {
        $program = $this->getProgram();
        if (! $program) {
            return 0;
        }

        return round($points * $program->redemption_rate, 2);
    }

    public function getBalance(User $user): int
    {
        return $user->points_balance;
    }

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<LoyaltyTransaction>
     */
    public function getHistory(User $user, int $perPage = 15): mixed
    {
        return LoyaltyTransaction::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function adjustPoints(User $user, int $points, string $description): LoyaltyTransaction
    {
        return DB::transaction(function () use ($user, $points, $description) {
            $newBalance = $user->points_balance + $points;
            $user->update(['points_balance' => max(0, $newBalance)]);

            return LoyaltyTransaction::create([
                'user_id' => $user->id,
                'points' => $points,
                'type' => 'adjusted',
                'description' => $description,
                'balance_after' => max(0, $newBalance),
            ]);
        });
    }
}

<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\StockAlert;
use App\Models\StockTransfer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Add stock to a product (Purchase, Return, Positive Adjustment)
     */
    public function addStock(
        Product $product,
        int $quantity,
        float $unitCost,
        string $type,
        ?Model $reference = null,
        ?int $userId = null,
        ?string $notes = null,
        ?int $warehouseLocationId = null,
    ): InventoryMovement {
        return DB::transaction(function () use ($product, $quantity, $unitCost, $type, $reference, $userId, $notes, $warehouseLocationId) {
            // Calculate WAC before incrementing (needs old quantity)
            if ($unitCost > 0 && in_array($type, ['purchase', 'initial_balance'])) {
                $newCost = $this->calculateWeightedAverageCost($product, $quantity, $unitCost);
            }

            $product->increment('quantity', $quantity);

            if (isset($newCost)) {
                $product->update(['cost' => $newCost]);
            }

            $totalCost = $quantity * $unitCost;
            $newBalance = $product->refresh()->quantity;

            $movement = InventoryMovement::create([
                'product_id' => $product->id,
                'type' => $type,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'balance_quantity' => $newBalance,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
                'user_id' => $userId ?? auth()->id(),
                'notes' => $notes,
                'warehouse_location_id' => $warehouseLocationId,
            ]);

            $this->checkAndCreateAlerts($product);

            return $movement;
        });
    }

    /**
     * Remove stock from a product (Sale, Negative Adjustment)
     */
    public function removeStock(
        Product $product,
        int $quantity,
        string $type,
        ?Model $reference = null,
        ?int $userId = null,
        ?string $notes = null,
        ?int $warehouseLocationId = null,
    ): InventoryMovement {
        return DB::transaction(function () use ($product, $quantity, $type, $reference, $userId, $notes, $warehouseLocationId) {
            $unitCost = $product->cost ?? 0;
            $totalCost = $quantity * $unitCost;

            $product->decrement('quantity', $quantity);
            $newBalance = $product->refresh()->quantity;

            $movement = InventoryMovement::create([
                'product_id' => $product->id,
                'type' => $type,
                'quantity' => -1 * abs($quantity),
                'unit_cost' => $unitCost,
                'total_cost' => -1 * abs($totalCost),
                'balance_quantity' => $newBalance,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
                'user_id' => $userId ?? auth()->id(),
                'notes' => $notes,
                'warehouse_location_id' => $warehouseLocationId,
            ]);

            $this->checkAndCreateAlerts($product);

            return $movement;
        });
    }

    /**
     * Calculate weighted average cost when adding stock.
     */
    public function calculateWeightedAverageCost(Product $product, int $newQuantity, float $newUnitCost): float
    {
        $currentQuantity = $product->quantity;
        $currentCost = $product->cost ?? 0;

        $totalExistingValue = $currentQuantity * $currentCost;
        $totalNewValue = $newQuantity * $newUnitCost;
        $totalQuantity = $currentQuantity + $newQuantity;

        if ($totalQuantity <= 0) {
            return $newUnitCost;
        }

        return round(($totalExistingValue + $totalNewValue) / $totalQuantity, 2);
    }

    /**
     * Process a stock transfer between warehouse locations.
     */
    public function processTransfer(StockTransfer $transfer): void
    {
        DB::transaction(function () use ($transfer) {
            foreach ($transfer->items as $item) {
                $this->removeStock(
                    product: $item->product,
                    quantity: $item->quantity,
                    type: 'transfer_out',
                    reference: $transfer,
                    notes: "Transferencia {$transfer->transfer_number} → {$transfer->toLocation->name}",
                    warehouseLocationId: $transfer->from_location_id,
                );

                $this->addStock(
                    product: $item->product,
                    quantity: $item->quantity,
                    unitCost: $item->product->cost ?? 0,
                    type: 'transfer_in',
                    reference: $transfer,
                    notes: "Transferencia {$transfer->transfer_number} ← {$transfer->fromLocation->name}",
                    warehouseLocationId: $transfer->to_location_id,
                );
            }

            $transfer->update([
                'status' => 'completed',
                'completed_at' => now(),
                'approved_by' => auth()->id(),
            ]);
        });
    }

    /**
     * Check stock levels and create alerts automatically.
     */
    public function checkAndCreateAlerts(Product $product): void
    {
        $product->refresh();

        if ($product->quantity <= 0) {
            $this->createAlertIfNotExists($product, 'out_of_stock', 0);
        } elseif ($product->quantity <= $product->low_stock_threshold) {
            $this->createAlertIfNotExists($product, 'low_stock', $product->low_stock_threshold);
        } else {
            // Stock is healthy — resolve any pending alerts
            StockAlert::where('product_id', $product->id)
                ->unresolved()
                ->whereIn('type', ['low_stock', 'out_of_stock'])
                ->each(fn (StockAlert $alert) => $alert->resolve());
        }
    }

    /**
     * Get Kardex report for a product within a date range.
     *
     * @return Collection<int, InventoryMovement>
     */
    public function getKardexReport(Product $product, ?string $dateFrom = null, ?string $dateTo = null): Collection
    {
        $query = InventoryMovement::where('product_id', $product->id)
            ->orderBy('created_at')
            ->orderBy('id');

        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo . ' 23:59:59');
        }

        return $query->get();
    }

    protected function createAlertIfNotExists(Product $product, string $type, int $threshold): void
    {
        $exists = StockAlert::where('product_id', $product->id)
            ->where('type', $type)
            ->unresolved()
            ->exists();

        if (! $exists) {
            StockAlert::create([
                'product_id' => $product->id,
                'type' => $type,
                'threshold' => $threshold,
                'current_quantity' => $product->quantity,
                'status' => 'pending',
            ]);
        }
    }
}

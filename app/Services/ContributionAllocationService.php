<?php

namespace App\Services;

use App\Models\MonthlyContribution;
use App\Models\InvestmentUnit;
use Illuminate\Support\Facades\DB;

class ContributionAllocationService
{
    public function allocate($userId, $totalAmount, $month)
    {
        DB::beginTransaction();

        try {
            $welfare = $totalAmount * 0.30;
            $investment = $totalAmount * 0.60;
            $operations = $totalAmount * 0.10;

            $contribution = MonthlyContribution::create([
    'user_id' => $userId,
    'total_amount' => $totalAmount,
    'welfare_amount' => $welfare,
    'investment_amount' => $investment,
    'operations_amount' => $operations,
    'month' => $month . '-01',   // ← fixed
    'status' => 'allocated'
    ]);

            // Update Investment Units
            $unit = InvestmentUnit::firstOrCreate(['user_id' => $userId]);
            $unitPrice = 100; // Example unit price - make dynamic later
            $newUnits = $investment / $unitPrice;

            $unit->total_units += $newUnits;
            $unit->total_invested += $investment;
            $unit->average_price = $unit->total_invested / $unit->total_units;
            $unit->save();

            DB::commit();

            return $contribution;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
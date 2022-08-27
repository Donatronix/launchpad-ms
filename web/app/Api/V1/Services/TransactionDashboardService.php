<?php

namespace App\Api\V1\Services;

use App\Models\Transaction;
use Carbon\Carbon;

class TransactionDashboardService
{
    public static function getTransactionsStatistics(): array
    {
        $query = new Transaction;
        $newTransactionsByDay = $query->whereBetween('created_at', self::getPeriod('day'));
        $newTransactionsByWeek = $query->whereBetween('created_at', self::getPeriod('week'));
        $newTransactionsByMonth = $query->whereBetween('created_at', self::getPeriod('month'));
        $newTransactionsByYear = $query->whereBetween('created_at', self::getPeriod('year'));

        //Group by Wallet
        $newTransactionsByWalletPerWeek = $newTransactionsByWeek->groupBy('wallet_address')
            ->selectRaw('wallet_address, count(*) as total')->get();
        $newTransactionsByWalletPerMonth = $newTransactionsByMonth->groupBy('wallet_address')
        ->selectRaw('wallet_address, count(*) as total')->get();
        $newTransactionsByWalletPerYear = $newTransactionsByYear->groupBy('wallet_address')
        ->selectRaw('wallet_address, count(*) as total')->get();
        $newTransactionsByWalletPerDay = $newTransactionsByDay->groupBy('wallet_address')
        ->selectRaw('wallet_address, count(*) as total')->get();

        //group by PaymentGateway
        $newTransactionsByPaymentGatewayPerWeek = $newTransactionsByWeek->groupBy('wallet_address')
            ->selectRaw('wallet_address, count(*) as total')->get();
        $newTransactionsByPaymentGatewayPerMonth = $newTransactionsByMonth->groupBy('wallet_address')
        ->selectRaw('wallet_address, count(*) as total')->get();
        $newTransactionsByPaymentGatewayPerYear = $newTransactionsByYear->groupBy('wallet_address')
        ->selectRaw('wallet_address, count(*) as total')->get();
        $newTransactionsByPaymentGatewayPerDay = $newTransactionsByDay->groupBy('wallet_address')
        ->selectRaw('wallet_address, count(*) as total')->get();

        //group by Stages
        $newTransactionsByStagesPerDay = $newTransactionsByDay->groupBy('token_stage')
        ->selectRaw('token_stage, count(*) as total')->get();
        $newTransactionsByStagesPerWeek = $newTransactionsByWeek->groupBy('token_stage')
        ->selectRaw('token_stage, count(*) as total')->get();
        $newTransactionsByStagesPerMonth = $newTransactionsByMonth->groupBy('token_stage')
        ->selectRaw('token_stage, count(*) as total')->get();
        $newTransactionsByStagesPerYear = $newTransactionsByYear->groupBy('token_stage')
        ->selectRaw('token_stage, count(*) as total')->get();

        return [
            'transactions_stat_by_day_count' => $newTransactionsByDay->count(),
            'transactions_stat_by_week_count' => $newTransactionsByWeek->count(),
            'transactions_stat_by_month_count' => $newTransactionsByMonth->count(),
            'transactions_stat_by_year_count' => $newTransactionsByYear->count(),

            // Wallets
            'transactions_stat_by_wallets_per_day' => $newTransactionsByWalletPerDay,
            'transactions_stat_by_wallets_per_week' => $newTransactionsByWalletPerWeek,
            'transactions_stat_by_wallets_per_month' => $newTransactionsByWalletPerMonth,
            'transactions_stat_by_wallets_per_year' => $newTransactionsByWalletPerYear,

            // Payment Gateways
            'transactions_stat_by_payment_gateways_per_day' => $newTransactionsByPaymentGatewayPerDay,
            'transactions_stat_by_payment_gateways_per_week' => $newTransactionsByPaymentGatewayPerWeek,
            'transactions_stat_by_payment_gateways_per_month' => $newTransactionsByPaymentGatewayPerMonth,
            'transactions_stat_by_payment_gateways_per_year' => $newTransactionsByPaymentGatewayPerYear,

            // Payment Gateways
            'transactions_stat_by_stages_per_day' => $newTransactionsByStagesPerDay,
            'transactions_stat_by_stages_per_week' => $newTransactionsByStagesPerWeek,
            'transactions_stat_by_stages_per_month' => $newTransactionsByStagesPerMonth,
            'transactions_stat_by_stages_per_year' => $newTransactionsByStagesPerYear,
        ];
    }

    static public function getPeriod($time): array
    {
        return match ($time) {
            'week' => [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ],
            'month' => [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ],
            'year' => [
                Carbon::now()->startOfYear(),
                Carbon::now()->endOfYear(),
            ],
            'day' => [
                Carbon::now()->startOfDay(),
                Carbon::now()->endOfDay(),
            ],
            default => [
                Carbon::now()->startOfDay(),
                Carbon::now()->endOfDay(),
            ],
        };
    }
}

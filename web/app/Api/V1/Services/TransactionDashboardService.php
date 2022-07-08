<?php

namespace App\Api\V1\Services;

use App\Models\Transaction;
use Carbon\Carbon;

class TransactionDashboardService
{
    public static function getTransactionsStatistics(): array
    {
        $newTransactionsByDay = Transaction::whereBetween('created_at', self::getPeriod('day'));
        $newTransactionsByWeek = Transaction::whereBetween('created_at', self::getPeriod('week'));
        $newTransactionsByMonth = Transaction::whereBetween('created_at', self::getPeriod('month'));
        $newTransactionsByYear = Transaction::whereBetween('created_at', self::getPeriod('year'));

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
            'new_transactions_by_day_count' => $newTransactionsByDay->count(),
            'new_transactions_by_week_count' => $newTransactionsByWeek->count(),
            'new_transactions_by_month_count' => $newTransactionsByMonth->count(),
            'new_transactions_by_year_count' => $newTransactionsByYear->count(),

            //Wallets
            'new_transactions_by_wallets_per_day' => $newTransactionsByWalletPerDay,
            'new_transactions_by_wallets_per_week' => $newTransactionsByWalletPerWeek,
            'new_transactions_by_wallets_per_month' => $newTransactionsByWalletPerMonth,
            'new_transactions_by_wallets_per_year' => $newTransactionsByWalletPerYear,

            //PaymentGateways
            'new_transactions_by_payment_gateways_per_day' => $newTransactionsByPaymentGatewayPerDay,
            'new_transactions_by_payment_gateways_per_week' => $newTransactionsByPaymentGatewayPerWeek,
            'new_transactions_by_payment_gateways_per_month' => $newTransactionsByPaymentGatewayPerMonth,
            'new_transactions_by_payment_gateways_per_year' => $newTransactionsByPaymentGatewayPerYear,

            //PaymentGateways
            'new_transactions_by_stages_per_day' => $newTransactionsByStagesPerDay,
            'new_transactions_by_stages_per_week' => $newTransactionsByStagesPerWeek,
            'new_transactions_by_stages_per_month' => $newTransactionsByStagesPerMonth,
            'new_transactions_by_stages_per_year' => $newTransactionsByStagesPerYear,
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

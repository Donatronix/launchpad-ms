<?php

namespace App\Traits;

use App\Models\Product;
use App\Models\TokenReward;
use Illuminate\Support\Facades\Http;

trait CryptoConversionTrait
{
    protected $currencies = [];
    protected $stage = 5;

    /**
     * Get the token worth for a purchase.
     *
     * @param $amount
     * @param $token
     * @param $currency_type
     * @return object
     */
    protected function getTokenWorth($amount, $token, $currency_type): mixed
    {
        if ($currency_type == "crypto"){
            return $this->getCryptoTokenWorth($amount, $token);
        }else if ($currency_type == "fiat"){
            return $this->getFiatTokenWorth($amount, $token);
        }
    }

    /**
     * Get the token worth for a particular crypto.
     *
     * @param $amount
     * @param $token
     * @return object
     * @throws \Exception
     */
    protected function getCryptoTokenWorth($amount, $token): mixed
    {
        // get the sol equivalent for the currency
        $sol_rate = $this->getTokenExchangeRate('usd', "sol");

        $sol_equivalent = $sol_rate * $amount;

        // devalue sol by 66%
        $devalue = (66 * $sol_equivalent) / 100;
        $new_sol_value = $sol_equivalent - $devalue;

        // get dollar equivalent of SOL
        $sol_dol_rate = $this->getTokenExchangeRate("sol", "usd");
        $dol_equivalent = $sol_dol_rate * $new_sol_value;

        // convert dollar value to required token
        $product = Product::query()->where("ticker", $token)->byStage($this->stage)->first();
        $token_stage5_price = $product->price->price;

        $token_amount = $dol_equivalent / $token_stage5_price;

        // Give token reward bonus based on gross solana equivalent
        $reward = TokenReward::where("swap", "<=", intval($sol_equivalent))->where("deposit_amount", ">=", intval($sol_equivalent))->value("reward_bonus");
        if (!$reward) {
            $reward = TokenReward::where("swap", "<=", intval($sol_equivalent))->where("deposit_amount", "+++")->value("reward_bonus");
        }

        // Calculate token 10% bonus
        $bonus = ($reward / 100) * $token_amount;

        // Return result
        return [
            'amount' => ceil($token_amount),
            'bonus' => ceil($bonus),
            'total' => ceil($token_amount + $bonus)
        ];
    }

    /**
     * Get the token worth for a particular crypto.
     *
     * @param $amount
     * @param $token
     * @return object
     */
    protected function getFiatTokenWorth($amount, $token): mixed
    {
        // convert dollar value to required token
        $product = Product::query()->where("ticker", $token)->byStage($this->stage)->first();
        $token_stage5_price = $product->price->price;

        $token_amount = $amount / $token_stage5_price;

        // Return result
        return [
            'amount' => ceil($token_amount),
            'bonus' => 0,
            'total' => ceil($token_amount)
        ];
    }

    /**
     * @param $from
     * @param $to
     * @return float|int
     * @throws \Exception
     */
    protected function getTokenExchangeRate($from, $to): float|int
    {
        if (!sizeof($this->currencies)) {
            /**
             * Prep reference books endpoint
             */
            $url = config('settings.api.reference_books') . '/currencies/rates/';

            /**
             * verify the code
             */
            $resp = Http::withHeaders([
                "user-id" => auth()->user()->getAuthIdentifier()
            ])->get($url);

            /**
             * Handle Response
             */
            if (!$resp->successful()) {
                $status = $resp->status() ?? 400;
                $message = $resp->getReasonPhrase() ?? 'Error Processing Request';

                throw new \Exception($message, $status);
            }

            $this->currencies = (array)$resp->object()->data ?? [];
        }

        if (sizeof($this->currencies)) {
            $from_rate = $this->currencies[$from] ?? 1;
            $to_rate = $this->currencies[$to] ?? 1;

            return $from_rate / $to_rate;
        }

        return 0;
    }
}

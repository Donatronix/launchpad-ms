<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Str;
use App\Models\Product;
use Illuminate\Support\Facades\Http;

trait CryptoConversionTrait
{
    protected $currencies = [];

    /**
     * Get the token worth for a particular crypt.
     *
     * @return string
     */
    protected function getTokenWorth($currency_ticker, $amount, $token): mixed
    {
        // get the sol equivalent for the currency
        $sol_rate = $this->getTokenExchangeRate($currency_ticker, "sol");

        $sol_equivalent = $sol_rate * $amount;

        // devalue sol by 66% 
        $devalue = (66 * $sol_equivalent) / 100;
        $new_sol_value = $sol_equivalent - $devalue;

        // get dollar equivalent of SOL
        $sol_dol_rate = $this->getTokenExchangeRate("sol", "usd");
        $dol_equivalent = $sol_dol_rate * $new_sol_value;

        // convert dollar value to required token
        $product = Product::query()->where("ticker", $token)->byStage(4)->first();
        $token_stage4_price = $product->price->price;

        $token_equivalent = $dol_equivalent / $token_stage4_price;

        // Calculate token 10% bonus
        $bonus = 0.1 * $token_equivalent;

        // get total token
        $total_token = $token_equivalent + $bonus;

        return $total_token;
    }

    /**
     * @return string
     */
    protected function getTokenExchangeRate($from, $to): mixed
    {
        if (!sizeof($this->currencies)) {
            /**
             * Prep reference books endpoint
             *
             */
            $endpoint = "/currencies/rates/";

            $reference_books_url = env("API_REFERENCE_BOOKS_URL");
            $url = $reference_books_url . $endpoint;

            /**
             * verify the code
             *
             */
            $resp = Http::withHeaders([
                "user-id" => $this->user_id
            ])->get($url);

            /**
             * Handle Response
             *
             */
            if (!$resp->successful()) {
                $status = $resp->status() ?? 400;
                $message = $resp->getReasonPhrase() ?? 'Error Processing Request';
                throw new \Exception($message, $status);
            }

            $this->currencies = $resp->object()->data ?? null;
        }

        if (sizeof($this->currencies)) {
            // Search for rates of currencies using symbol
            $from_key = array_search(mb_strtoupper($from), array_column($this->currencies, "currency"));
            $from_rate = $this->currencies[$from_key]->rate;

            $to_key = array_search(mb_strtoupper($to), array_column($this->currencies, "currency"));
            $to_rate = $this->currencies[$to_key]->rate;

            $rate = $from_rate / $to_rate;

            return $rate;
        }
    }
}

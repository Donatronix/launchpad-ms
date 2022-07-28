<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Str;
use App\Models\Product;

trait CryptoConversionTrait
{

    /**
     * Get the token worth for a particular token.
     *
     * @return string
     */
    protected function getTokenWorth($crypto, $amount, $token): mixed
    {
        // get the sol equivalent for the crypto
        // $crypto_sol_rate = $this->getTokenExchangeRate($crypto, "sol");
        $crypto_sol_rate = 572;
        $sol_equivalent = $crypto_sol_rate * $amount;

        // devalue sol by 66% 
        $devalue = (66 * $sol_equivalent) / 100;
        $new_sol_value = $sol_equivalent - $devalue;

        // get dollar equivalent of SOL
        // $sol_dol_rate = $this->getTokenExchangeRate("sol", "dollar");
        $sol_dol_rate = 35;
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
        // try {
        //     /**
        //      * Prep reference books endpoint
        //      *
        //      */
        //     $endpoint = '/admin/users?type=Investor';
        //     $params = $request->getQueryString();
        //     if ($params) {
        //         $endpoint = $endpoint . '&' . $params;
        //     }
        //     $IDS = env("API_REFERENCE_BOOKS_HOST");
        //     $url = $IDS['host'] . '/' . $IDS['version'] . $endpoint;

        //     /**
        //      * Get Details from IDS
        //      *
        //      */
        //     $response = Http::withToken($request->bearerToken())->withHeaders([
        //         'User-Id' => Auth::user()->getAuthIdentifier()
        //     ])->get($url);

        //     /**
        //      * Handle Response
        //      *
        //      */
        //     if (!$response->successful()) {
        //         $status = $response->status() ?? 400;
        //         $message = $response->getReasonPhrase() ?? 'Error Processing Request';

        //         throw new Exception($message, $status);
        //     }

        //     $investors = [];
        //     $data = $response->object()->data ?? null;

        //     if ($data) {
        //         /**
        //          * Get Tokens
        //          *
        //          */
        //         // foreach ($data->data as $key => $investor) {
        //         //     $tokens = Purchase::where([
        //         //         'user_id' => $investor->id
        //         //     ])->sum("token_amount");

        //         //     $investor->tokens = $tokens;
        //         //     $investors[] = $investor;
        //         // }

        //         /**
        //          * Client Response
        //          *
        //          */
        //         $data->data = $investors;
        //     }
        // } catch (Exception $e) {
        // }
    }
}

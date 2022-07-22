<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Str;

trait CryptoConversionTrait
{

    /**
     * Get the token worth for a particular token.
     *
     * @return string
     */
    protected function getTokenWorth($crypto, $amount): mixed
    {
        // get the sol equivalent for the crypto
    }

    /**
     * @return string
     */
    protected function getTokenRate(): mixed
    {
        try {
            /**
             * Prep reference books endpoint
             *
             */
            $endpoint = '/admin/users?type=Investor';
            $params = $request->getQueryString();
            if ($params) {
                $endpoint = $endpoint . '&' . $params;
            }
            $IDS = env("API_REFERENCE_BOOKS_HOST");
            $url = $IDS['host'] . '/' . $IDS['version'] . $endpoint;

            /**
             * Get Details from IDS
             *
             */
            $response = Http::withToken($request->bearerToken())->withHeaders([
                'User-Id' => Auth::user()->getAuthIdentifier()
            ])->get($url);

            /**
             * Handle Response
             *
             */
            if (!$response->successful()) {
                $status = $response->status() ?? 400;
                $message = $response->getReasonPhrase() ?? 'Error Processing Request';

                throw new Exception($message, $status);
            }

            $investors = [];
            $data = $response->object()->data ?? null;

            if ($data) {
                /**
                 * Get Tokens
                 *
                 */
                // foreach ($data->data as $key => $investor) {
                //     $tokens = Purchase::where([
                //         'user_id' => $investor->id
                //     ])->sum("token_amount");

                //     $investor->tokens = $tokens;
                //     $investors[] = $investor;
                // }

                /**
                 * Client Response
                 *
                 */
                $data->data = $investors;
            }
        } catch (Exception $e) {
        }
    }
}

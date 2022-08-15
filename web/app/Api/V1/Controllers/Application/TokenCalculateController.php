<?php

namespace App\Api\V1\Controllers\Application;

use App\Api\V1\Controllers\Controller;
use App\Models\Product;
use App\Traits\CryptoConversionTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TokenCalculateController extends Controller
{
    use CryptoConversionTrait;

    /**
     * Token purchase calculation
     *
     * @OA\Post(
     *     path="/app/token-calculate",
     *     summary="Token purchase calculation",
     *     description="Token purchase calculation. Currency should be btc, eth, usd, gbp or eur",
     *     tags={"Application | Calculation Purchasing Token"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="product_id",
     *                 type="string",
     *                 description="Product ID",
     *                 example="9a778e5d-61aa-4a2b-b511-b445f6a67909"
     *             ),
     *             @OA\Property(
     *                 property="investment_amount",
     *                 type="integer",
     *                 description="Amount spent to pay in fiat",
     *                 example="5000"
     *             ),
     *             @OA\Property(
     *                 property="currency",
     *                 type="string",
     *                 description="Currency in which payment will be made",
     *                 example="btc/eth/sol/bnb/usd/eur/gbp"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="ok",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Created",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error",
     *         @OA\JsonContent(ref="#/components/schemas/DangerResponse")
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(ref="#/components/schemas/WarningResponse")
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Validation Failed",
     *         @OA\JsonContent(ref="#/components/schemas/WarningResponse")
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     )
     * )
     *
     * @param Request $request
     * @return mixed
     */
    public function __invoke(Request $request): mixed
    {
        // Try to save purchased token data
        try {
            $result = $this->handle($request);

            // Return response to client
            return response()->jsonApi([
                'title' => 'Token purchase calculation',
                'message' => 'Calculation completed successfully',
                'data' => [
                    'currency' => $request->currency,
                    'rate' => $result['rate'],
                    'payment_amount' => $result['payment_amount'],
                    "token_amount" => $result['token']['amount'],
                    "bonus" => $result['token']['bonus'],
                    "total_token" => $result['token']['total'],
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'title' => 'Token purchase calculation',
                'message' => 'Validation error occurred!',
                'data' => $e->getMessage()
            ], 422);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Token purchase calculation',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * @param $request
     * @return array
     * @throws Exception
     */
    private function handle($request): mixed
    {
        $rules = [
            'product_id' => 'required|string',
            'currency' => 'required|string'
        ];

        // Convert currency
        $currency = strtolower($request->get('currency'));
        if (in_array($currency, ['usd', 'eur', 'gbp'])) {
            $currency_type = 'fiat';

            $rules += [
                'investment_amount' => 'required|numeric|min:250|max:1000',
            ];
        } else {
            $currency_type = 'crypto';

            $rules += [
                'investment_amount' => 'required|numeric',
            ];
        }

        // Do validate input data
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            throw new ValidationException('Validation error occurred!', 422);
        }

        // get product details
        if ($request->has("product_ticker")) {
            $product_ticker = $request->product_ticker;
        } else if ($request->has("product_id")) {
            $product = Product::find($request->get('product_id'));
            if (!$product) {
                throw new Exception('Product not found', 400);
            }
            $product_ticker = $product->ticker;
        } else {
            throw new Exception('Product is required', 400);
        }

        // get rate of token
        $result['rate'] = $this->getTokenExchangeRate('usd', $currency);

        // Get payment_amount
        $result['payment_amount'] = $result['rate'] * $request->investment_amount;

        // Get calculated token
        $result['token'] = $this->getTokenWorth($request->investment_amount, $product_ticker, $currency_type);

        $result['currency_type'] = $currency_type;
        // return response
        return $result;
    }
}

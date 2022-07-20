<?php

namespace App\Api\V1\Controllers\Admin;

use App\Api\V1\Controllers\Controller;
use App\Models\Purchase;
use Auth;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class InvestorController extends Controller
{
    /**
     * Display a listing of Investor
     *
     * @OA\Get(
     *     path="/admin/investors",
     *     description="Get list of Investor users",
     *     tags={"Admin | Investors"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search string",
     *         required=false,
     *         @OA\Schema(
     *              type="string",
     *              default=20,
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         description="Number of expected data in response",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *              type="integer",
     *              default=20,
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         description="Page of list",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *              type="integer",
     *              default=1,
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Data fetched",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error",
     *         @OA\JsonContent(ref="#/components/schemas/DangerResponse")
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/DangerResponse")
     *     )
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try {
            /**
             * Prep IDS endpoint
             *
             */
            $endpoint = '/admin/users?type=Investor';
            $params = $request->getQueryString();
            if ($params) {
                $endpoint = $endpoint . '&' . $params;
            }
            $IDS = config('settings.api.identity');
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
                foreach ($data->data as $key => $investor) {
                    $tokens = Purchase::where([
                        'user_id' => $investor->id
                    ])->sum("token_amount");

                    $investor->tokens = $tokens;
                    $investors[] = $investor;
                }

                /**
                 * Client Response
                 *
                 */
                $data->data = $investors;
            }

            return response()->jsonApi([
                'title' => 'Get Investor',
                'message' => 'Avaialable Investors',
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Get Investor',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}

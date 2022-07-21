<?php

namespace App\Api\V1\Controllers\Admin;

use Illuminate\Http\Request;
use App\Api\V1\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Auth;
use App\Models\Purchase;

class AdminController extends Controller
{
    /**
     * Display a listing of Admin
     *
     * @OA\Get(
     *     path="/admin",
     *     description="Get list of Admin users",
     *     tags={"Admin | Admins"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
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
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            /**
             * Prep IDS endpoint
             *
             */
            $endpoint = '/admin/users?type=Admin';
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
                throw new \Exception($message, $status);
            }

            $admins = [];
            $data = $response->object()->data ?? null;
            if ($data) {
                /**
                 * Get Tokens
                 *
                 */
                foreach ($data->data as $key => $admin) {
                    $tokens = Purchase::where([
                        'user_id' => $admin->id
                    ])->sum("token_amount");

                    $admin->tokens = $tokens;
                    $admins[] = $admin;
                }

                /**
                 * Client Response
                 *
                 */
                $data->data = $admins;
            }

            return response()->jsonApi([
                'title' => 'Get Admins',
                'message' => 'Avaialable Admins',
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Get Admin',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}

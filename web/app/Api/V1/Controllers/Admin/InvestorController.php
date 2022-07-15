<?php

namespace App\Api\V1\Controllers\Admin;

use App\Api\V1\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Purchase;
use Illuminate\Support\Facades\Http;
use Auth;

class InvestorController extends Controller
{
    /**
     * Display a listing of Investor
     *
     * @OA\Get(
     *     path="/admin/investors",
     *     description="Get list of Investor users",
     *     tags={"Admin / Investors"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
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
     *     @OA\Response(
     *         response=200,
     *         description="Success",
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
                throw new \Exception("Error Processing Request", 500);
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
                'type' => 'success',
                'title' => 'Get Investor',
                'message' => 'Avaialable Investors',
                'data' => $data,
            ], 200);
        }
        catch (\Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Get Investor',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Create new Resource
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
  'message' => $e->getMessage(),
                'data' => null
            ], 400);
        }

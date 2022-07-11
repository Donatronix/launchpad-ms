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
     *     tags={"Admin / Admins"},
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
            $response = Http::withHeaders([
                'User-Id' => Auth::user()->getAuthIdentifier()
            ])->get($url);

            /**
             * Handle Response
             *
             */
            if (!$response->successful()) {
                throw new \Exception("Error Processing Request", 500);
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
                'type' => 'success',
                'title' => 'Get Admins',
                'message' => 'Avaialable Admins',
                'data' => $data
            ], 200);
        }
        catch (\Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Get Admin',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Store a newly created resource in storage.
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
    }
}

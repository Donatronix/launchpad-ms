<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TokenReward;
use Exception;
use Illuminate\Http\Request;

class TokenRewardController extends Controller
{
    /**
     * Method for list of user's tokenReward.
     *
     * @OA\Get(
     *     path="/token-rewards",
     *     description="Get list of un-approved user's tokenReward",
     *     tags={"Admin / TokenRewards"},
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
     *         name="limit",
     *         description="Count of token rewards in one page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=20
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *     )
     * )
     *
     * Method for list of token Reward of users.
     *
     * @param Request $request
     *
     * @return
     *
     * @throws Exception
     */
    public function index(Request $request)
    {
        try {
            $result = TokenReward::paginate($request->get('limit', 20));

            // Return response
            return response()->jsonApi($result->toArray());
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Token rewards list',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}

<?php

namespace App\Api\V1\Controllers\Public;

use App\Api\V1\Controllers\Controller;
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
     *     summary="Get list of un-approved user's tokenReward",
     *     description="Get list of un-approved user's tokenReward",
     *     tags={"Public | Token Rewards"},
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
     *         description="Unknown error"
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
    public function __invoke(Request $request)
    {
        try {
            $list = TokenReward::paginate($request->get('limit', config('settings.pagination_limit')));

            // Return response
            return response()->jsonApi([
                'title' => 'Token rewards list',
                'message' => 'Token Rewards List received successfully',
                'data' => $list
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Token rewards list',
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }
}

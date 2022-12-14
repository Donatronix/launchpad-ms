<?php

namespace App\Api\V1\Controllers\Admin;

use App\Api\V1\Controllers\Controller;
use App\Models\TokenReward;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TokenRewardController extends Controller
{
    /**
     * Method for list of user's tokenReward.
     *
     * @OA\Get(
     *     path="/admin/token-rewards",
     *     description="Get list of un-approved user's tokenReward",
     *     tags={"Admin | TokenRewards"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="limit",
     *         description="Count of token rewards in one page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *              default=20
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Data fetched",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="New record addedd successfully",
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
            $result = TokenReward::paginate($request->get('limit', config('settings.pagination_limit')));

            // Return response
            return response()->jsonApi([
                'title' => 'Token rewards list',
                'message' => 'Token lists received',
                'data' => $result
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Token rewards list',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Method for show user's token reward
     *
     * @OA\Get(
     *     path="/admin/token-rewards/{id}",
     *     description="Get tokenReward of user by id",
     *     tags={"Admin | TokenRewards"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="id",
     *         description="TokenReward ID",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Data fetched",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="New record addedd successfully",
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
     * @param         $id
     *
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $tokenReward = TokenReward::find($id);

            if (!$tokenReward) {
                return response()->jsonApi([
                    'title' => 'Get a token',
                    'message' => 'No tokenReward of user with id=' . $id,
                ], 400);
            }

            return response()->jsonApi([
                'title' => 'Get a token',
                'message' => 'Token received',
                'data' => $tokenReward,
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Get token data error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Method for storage of user's token Reward.
     *
     * @OA\Post(
     *     path="/admin/token-rewards",
     *     description="Store user's tokenReward",
     *     tags={"Admin | TokenRewards"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *       @OA\RequestBody(
     *            @OA\JsonContent(
     *                type="object",
     *                @OA\Property(
     *                    property="purchase_band",
     *                    type="string",
     *                    description="Serial number of purchase",
     *                ),
     *                @OA\Property(
     *                    property="swap",
     *                    type="string",
     *                    description="Number of tokens",
     *                ),
     *                @OA\Property(
     *                    property="deposit_amount",
     *                    type="integer",
     *                    description="Amount money to be deposited",
     *                ),
     *                @OA\Property(
     *                    property="reward_bonus",
     *                    type="integer",
     *                    description="Reward bonus for token",
     *                )
     *           )
     *       ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Data fetched",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="New record addedd successfully",
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
     *     ),
     * )
     *
     * Method for storage of token Reward of users.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function store(Request $request): JsonResponse
    {
        $tokenReward = null;
        try {
            DB::transaction(function () use ($request, &$tokenReward) {
                $tokenReward = TokenReward::create($request->all());
            });
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Reward token',
                'message' => $e->getMessage()
            ], 400);
        }
        return response()->jsonApi([
            'title' => "Reward token",
            'message' => "Reward token created",
            'data' => $tokenReward
        ], 201);
    }

    /**
     * Update user's token Reward.
     *
     * @OA\Put(
     *     path="/admin/token-rewards",
     *     description="update user's tokenReward",
     *     tags={"Admin | TokenRewards"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="id",
     *         description="TokenReward ID",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="purchase_band",
     *                 type="string",
     *                 description="Serial number of purchase",
     *             ),
     *             @OA\Property(
     *                 property="swap",
     *                 type="string",
     *                 description="Number of tokens",
     *             ),
     *             @OA\Property(
     *                 property="deposit_amount",
     *                 type="integer",
     *                 description="Amount money to be deposited",
     *             ),
     *             @OA\Property(
     *                 property="reward_bonus",
     *                 type="integer",
     *                 description="Reward bonus for token",
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Data fetched",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="New record addedd successfully",
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
     * Method for update of token Reward of users.
     *
     * @param Request $request
     * @param TokenReward $tokenReward
     *
     * @return JsonResponse
     *
     */
    public function update(Request $request, TokenReward $tokenReward): JsonResponse
    {
        try {
            DB::transaction(function () use ($request, &$tokenReward) {
                $tokenReward->update($request->all());
            });
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Reward token',
                'message' => $e->getMessage(),
            ], 400);
        }
        return response()->jsonApi([
            'title' => "Reward token",
            'message' => "Reward token received",
            'data' => $tokenReward
        ]);
    }

    /**
     * Method for delete tokenReward by id
     *
     * @OA\Delete(
     *     path="/admin/token-rewards/{id}",
     *     description="destroy user's token rewards by id",
     *     tags={"Admin | TokenRewards"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="id",
     *         description="TokenReward ID",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Data fetched",
     *         @OA\JsonContent(ref="#/components/schemas/OkResponse")
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="New record addedd successfully",
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
     * @param $id
     *
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $tokenReward = TokenReward::find($id);

            if (!$tokenReward)
                return response()->jsonApi([
                    'title' => 'Reward token',
                    'message' => 'No token reward  with id=' . $id,
                ], 400);

            $tokenReward->delete();

            return response()->jsonApi([
                'title' => "Reward token",
                'message' => "Reward token received",
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Reward token',
                'message' => 'No token reward  with id=' . $id,
            ], 400);
        }
    }
}

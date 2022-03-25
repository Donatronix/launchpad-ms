<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\TokenRewardResource;
use App\Models\TokenReward;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Throwable;

class TokenRewardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return TokenRewardResource::collection(TokenReward::all());
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return TokenRewardResource
     */
    public function store(Request $request)
    {
        $tokenReward = null;
        try {
            DB::transaction(function () use ($request, &$tokenReward) {
                $tokenReward = TokenReward::create($request->all());
            });
        } catch (Throwable $th) {
            return $th->getMessage();
        }
        return new TokenRewardResource($tokenReward);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Request     $request
     * @param TokenReward $tokenReward
     *
     * @return TokenRewardResource
     */
    public function update(Request $request, TokenReward $tokenReward)
    {
        try {
            DB::transaction(function () use ($request, &$tokenReward) {
                $tokenReward->update($request->all());
            });
        } catch (Throwable $th) {
            return $th->getMessage();
        }
        return new TokenRewardResource($tokenReward);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param TokenReward $tokenReward
     *
     * @return Response
     */
    public function destroy(TokenReward $tokenReward)
    {
        try {
            DB::transaction(function () use ($tokenReward) {
                $tokenReward->delete();
            });
        } catch (Throwable $th) {
            return $th->getMessage();
        }
        return 'success';
    }
}

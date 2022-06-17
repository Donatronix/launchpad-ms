<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;

use App\Models\Faq;
use App\Traits\ResponseTrait;

class FaqController extends Controller
{
    use ResponseTrait;

    /**
     * Display a listing Faqs.
     *
     * @OA\Get(
     *     path="/faqs",
     *     description="Get all faqs",
     *     tags={"Faqs"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *     x={
     *         "auth-type": "Application & Application User",
     *         "throttling-tier": "Unlimited",
     *         "wso2-application-security": {
     *             "security-types": {"oauth2"},
     *             "optional": "false"
     *         }
     *     },
     *
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Per page limit",
     *         @OA\Schema(
     *             type="number"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Current page",
     *         @OA\Schema(
     *             type="number"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search keywords",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort[by]",
     *         in="query",
     *         description="Sort by field ()",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort[order]",
     *         in="query",
     *         description="Sort order (asc, desc)",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success send data"
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not found"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $limit = $request->limit ?? 10;

        try {
            $faqs = Faq::latest()->paginate($limit);
            return $this->processPaginator('Faqs', $faqs);
        }
        catch (\Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Faqs",
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a particular Faq based on ID.
     *
     * @OA\Get(
     *     path="/faqs/{id}",
     *     description="Get a faq",
     *     tags={"Faqs"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *     x={
     *         "auth-type": "Application & Application User",
     *         "throttling-tier": "Unlimited",
     *         "wso2-application-security": {
     *             "security-types": {"oauth2"},
     *             "optional": "false"
     *         }
     *     },
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Faq ID",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success send data"
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not found"
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $faq = Faq::findOrFail($id);
            return response()->jsonApi([
                'type' => 'success',
                'title' => "Faq",
                'message' => 'Faq loaded',
                'data' => $faq
            ], 200);
        }
        catch (\Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Faq",
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

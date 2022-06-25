<?php

namespace App\Api\V1\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    use ResponseTrait;

    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     path="/admin/faqs",
     *     description="Get all faqs",
     *     tags={"Admin / Faqs"},
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
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Faq",
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Create a new faq model
     *
     * @OA\Post(
     *     path="/admin/faqs",
     *     description="create faq",
     *     tags={"Admin / Faqs"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Faq")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Faq created Successfully"
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Faq created"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="not found"
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Validation failed"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'body' => 'required',
            'type' => 'required',
            'icon' => 'required'
        ]);

        try {
            $faq = Faq::create($request->all());
            return $this->createdResponse('Faq', $faq);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Display a particular Faq based on ID.
     *
     * @OA\Get(
     *     path="/admin/faqs/{id}",
     *     description="Get a faq",
     *     tags={"Admin / Faqs"},
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
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Faq",
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Update a faq model
     *
     * @OA\Put(
     *     path="/admin/faqs/{id}",
     *     description="Update faq",
     *     tags={"Admin / Faqs"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Faq ID",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Faq")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Faq updated Successfully"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="not found"
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Validation failed"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title' => 'required',
            'body' => 'required',
            'type' => 'required',
            'icon' => 'required'
        ]);

        try {
            $faq = Faq::findOrFail($id);

            // Update
            $faq->fill($request->all());
            $faq->save();

            return $this->okResponse('Faq updated', $faq);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Delete a particular Faq based on ID.
     *
     * @OA\Delete(
     *     path="/admin/faqs/{id}",
     *     description="Get a faq",
     *     tags={"Admin / Faqs"},
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
     *         description="Success"
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
    public function destroy($id)
    {
        try {
            $faq = Faq::findOrFail($id);
            $faq->delete();
            return $this->okResponse('Faq Deleted');
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Faq",
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

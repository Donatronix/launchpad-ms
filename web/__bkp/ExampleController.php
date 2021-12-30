<?php

//namespace App\Api\V1\Controllers;

/**
 * Class Controller
 *
 * @package App\Api\V1\Controllers
 */
class ExampleController extends Controller
{
    /**
     * ExampleController constructor.
     * 
     * @param Contributor $model
     */
    public function __construct(Example $model)
    {
        $this->model = $model;
    }

    /**
     * Display a listing of the resource.
     *
     * @ OA \GET(
     *     path="/",
     *     description="Get shelters lists",
     *
     *     @OA\Parameter(
     *         description="Limit default 100",
     *         name="limit",
     *         in="query",
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *   @OA\Parameter(
     *         description="Offset",
     *         name="offset",
     *         in="query",
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="OK"
     *     )
     * )
     */
    public function index(GetRequest $request)
    {
        return BookResource::collection(Book::paginate(25));
        return parent::get($request);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @ OA \Post(
     *     path="/contributors",
     *     description="Add shelter",
     *     @OA\Parameter(
     *         description="Contributor name",
     *         name="name",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Contributor address",
     *         name="address",
     *         in="query",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *          response="201",
     *          description="Contributor created"
     *     ),
     *     @OA\Response(
     *          response="422",
     *          description="Validation failed"
     *     )
     * )
     */
    public function store(StoreRequest $request)
    {
        try {
            $book = new Book;
            $book->fill($request->validated())->save();

            return new BookResource($book);

        } catch (\Exception $exception) {
            throw new HttpException(400, "Invalid data - {$exception->getMessage}");
        }

        return parent::add($request);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $book = Book::findOrfail($id);

        return new BookResource($book);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, $id)
    {
        if (!$id) {
            throw new HttpException(400, "Invalid id");
        }

        try {
            $book = Book::find($id);
            $book->fill($request->validated())->save();

            return new BookResource($book);

        } catch (\Exception $exception) {
            throw new HttpException(400, "Invalid data - {$exception->getMessage}");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @ OA \Delete(
     *     path="/contributors/{id}",
     *     description="remove shelter by id",
     *     @OA\Parameter(
     *         description="ID of shelter to delete",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Contributor not found",
     *     ),
     *     @OA\Response(
     *          response="204",
     *          description="Delete shelter"
     *     )
     * )
     */
    public function destroy(int $id)
    {
        $book = Book::findOrfail($id);
        $book->delete();

        return parent::delete($id);
        return response()->json(null, 204);
    }
}

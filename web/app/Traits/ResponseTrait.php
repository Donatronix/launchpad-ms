<?php

namespace App\Traits;

/**
 *
 */
trait ResponseTrait
{
    public function processPaginator($message, $paginator)
    {
        $meta = [
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
            'next_page_url' => $paginator->nextPageUrl(),
            'previous_page_url' => $paginator->previousPageUrl(),
            'first_item' => $paginator->firstItem(),
            'last_item' => $paginator->lastItem(),
        ];

        return response([
            'type' => 'success',
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => $meta]);
    }

    public function createdResponse($message, $data)
    {
        return response([
            'type' => 'success',
            'message' => $message,
            'data' => $data], 201);
    }

    public function errorResponse($message)
    {
        return response([
            'type' => 'danger',
            'message' => $message], 500);
    }

    public function okResponse($message, $data = null)
    {
        return response([
            'type' => 'success',
            'message' => $message,
            'data' => $data], 200);
    }
}

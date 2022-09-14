<?php

    namespace App\Http\Resources;

    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class ProductResource extends JsonResource
    {
        /**
         * Transform the resource into an array.
         *
         * @param Request $request
         *
         * @return array
         */
        public function toArray($request)
        {
            return [
                'id' => $product->id,
                'title' => $product->title,
                'ticker' => $product->ticker,
                'supply' => $product->supply,
                'sold' => $product->sold,
                'presale_percentage' => $product->presale_percentage,
                'icon' => $product->icon,
                'start_date' => $product->start_date,
                'end_date' => $product->end_date,
                'status',
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
            ];
        }
    }

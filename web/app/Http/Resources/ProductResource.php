<?php

    namespace App\Http\Resources;

    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class thisResource extends JsonResource
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
                'id' => $this->id,
                'title' => $this->title,
                'ticker' => $this->ticker,
                'supply' => $this->supply,
                'sold' => $this->sold,
                'presale_percentage' => $this->presale_percentage,
                'icon' => $this->icon,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'status',
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ];
        }
    }

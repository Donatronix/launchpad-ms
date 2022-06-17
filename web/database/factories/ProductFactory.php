<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->title(),
            'ticker' => $this->faker->currencyCode(),
            'supply' => $this->faker->numberBetween(1000000, 1000000000000),
            'presale_percentage' => $this->faker->numberBetween(1, 10),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'status' => true,
            'icon' => "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAAAXNSR0IArs4c6QAAAC1JREFUWEft0EERAAAAAUH6lxbDZxU4s815PffjAAECBAgQIECAAAECBAgQIDAaPwAh6O5R/QAAAABJRU5ErkJggg==",
        ];
    }
}

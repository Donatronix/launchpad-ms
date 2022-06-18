<?php

namespace Database\Factories;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

class FaqFactory extends Factory
{
    protected $model = Faq::class;

    public function definition(): array
    {
    	return [
    	    'id' => $this->faker->uuid,
            'title' => $this->faker->sentence,
            'body' => $this->faker->text($maxNBChars = 300),
            'type' => $this->faker->word,
            'icon' => 'https://source.unsplash.com/random',
    	];
    }
}

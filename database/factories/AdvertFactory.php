<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Advert>
 */
class AdvertFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            'user_id' => function () {
                return User::factory()->create()->id;
            },
            'name' => $this->faker->sentence,
            'page' => $this->faker->word,
            'media_url' => $this->faker->imageUrl(),
            'mediaType' => $this->faker->randomElement(['image', 'video']),
            'duration' => $this->faker->numberBetween(1, 60),
            'width' => $this->faker->numberBetween(100, 1920),
            'height' => $this->faker->numberBetween(100, 1080),
            'country' => $this->faker->country,
            'platform' => $this->faker->randomElement(['desktop', 'mobile']),
            'section' => $this->faker->word,
            'redirect_url' => $this->faker->url,
        ];
    }
}

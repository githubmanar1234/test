<?php

namespace Database\Factories;

use App\Helpers\Constants;
use App\Models\Salon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SalonFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Salon::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->name,
            'city_id' => rand(1,10),
            'salon_code' => Str::random(6),
            // 'skills' => $this->faker->text(),
        ];
    }
}

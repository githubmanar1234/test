<?php

namespace Database\Factories;

use App\Helpers\Constants;
use App\Models\Barber;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BarberFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Barber::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'salon_id' => rand(1,10),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'city_id' => rand(1,10),
            'salon_code' => Str::random(6),
            // 'skills' => $this->faker->text(),
        ];
    }
}

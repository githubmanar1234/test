<?php

namespace Database\Factories;

use App\Helpers\Constants;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'phone' => $this->faker->unique()->phoneNumber,
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'country_id' => rand(1,10),
            'firebase_uid' => Str::random(10),
            'yob' => $this->faker->date(),
            'email' => $this->faker->email(),
            // 'skills' => $this->faker->text(),
            'fcm_token' => Str::random(10),
            // 'lang' => Constants::LANGUAGE_EN,
            // 'work_experience' => [1,2],
            // 'location' => "15.45,10.25",
        ];
    }
}

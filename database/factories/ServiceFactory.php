<?php

namespace Database\Factories;

use App\Helpers\Constants;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Service::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->word,
            'description' => $this->faker->text,
            'image' => "https://picsum.photos/id/1/200/300",
            'location' => $this->faker->randomFloat(),
            'have_store' => true,
            'subcategory_id' => null,
            'user_id' => null,
            'status' => Constants::STATUSES[rand(0,2)],
            'reject_message' => $this->faker->sentence,
            'facebook_link' => $this->faker->url,
            'phone_number' => $this->faker->phoneNumber,
            'whatsapp_number' => $this->faker->phoneNumber,
            'qualification' => $this->faker->word,
        ];
    }
}

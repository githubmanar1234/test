<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $faker = \Faker\Factory::create('ar');
        static $order=1;
        return [
            'title' => ["en" => $this->faker->word , "ar" => $faker->word,"fr" =>$faker->word],
            'description' => ["en" => $this->faker->text , "ar" => $faker->text,"fr" => $faker->text],
            'image' => "https://picsum.photos/id/1/200/300",
            'order' => $order++,
            'parent_id' => null
        ];
    }
}

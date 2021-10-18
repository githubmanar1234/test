<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\City;
use App\Models\Job;
use App\Models\JobDetail;
use App\Models\Service;
use App\Models\ServiceDetail;
use App\Models\Subservice;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $categories = Category::factory()->count(5)->create();
        // $city1 = new City(['name' => 'city11', 'country_id' => 1]);
        // $city1->save();
        // $city2 = new City(['name' => 'city22', 'country_id' => 1]);
        // $city2->save();
        // foreach ($categories as $category) {
        //     $subCategory1 = Category::factory()
        //         ->state(
        //             [
        //                 'parent_id' => $category->id,
        //                 'order' => 1,
        //                 'image' => 'https://picsum.photos/id/1048/200/300'
        //             ])->create();
        //     $user1 = User::factory()->create();

        //     $services1 = Service::factory()->count(2)->state(['user_id' => $user1->id, 'city_id'=>$city1->id ,'subcategory_id' => $subCategory1->id])->create();
        //     foreach ($services1 as $service) {
        //        // $servicesDetails = ServiceDetail::factory()->count(1)->state(['service_id' => $service->id])->create();
        //     }
        //     $job2 = Job::factory()->count(2)->state(['user_id' => $user1->id, 'city_id'=>$city1->id, 'subcategory_id' => $subCategory1->id])->create();
        //     foreach ($job2 as $job) {
        //      //   $jobDetails = JobDetail::factory()->count(1)->state(['job_id' => $job->id])->create();
        //     }
        //     $subCategory2 = Category::factory()
        //         ->state(
        //             [
        //                 'parent_id' => $category->id,
        //                 'order' => 2,
        //                 'image' => 'https://picsum.photos/id/1048/200/300'
        //             ])->create();
        //     $user2 = User::factory()->create();

        //     $services2 = Service::factory()->count(2)->state(['user_id' => $user2->id,'city_id'=>$city2->id, 'subcategory_id' => $subCategory2->id])->create();
        //     foreach ($services2 as $service) {
        //        // $servicesDetails = ServiceDetail::factory()->count(1)->state(['service_id' => $service->id])->create();
        //        // $servicesDetails = Subservice::factory()->count(3)->state(['service_id' => $service->id])->create();
        //     }
        //     $job2 = Job::factory()->count(2)->state(['user_id' => $user2->id,'city_id'=>$city2->id, 'subcategory_id' => $subCategory2->id])->create();
        //     foreach ($job2 as $job) {
        //        // $jobDetails = JobDetail::factory()->count(1)->state(['job_id' => $job->id])->create();
        //     }
        // }
    }
}

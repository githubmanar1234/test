<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!Admin::where('email', "admin@admin.com")->first())
            Admin::factory()->count(1)->state(['email' => "admin@admin.com"])->create();
        Admin::factory()->count(1)->state(['email' => "admin2@admin.com"])->create();
    }
}

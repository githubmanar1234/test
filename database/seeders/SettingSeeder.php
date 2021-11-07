<?php

namespace Database\Seeders;


use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::query()->delete();
        //
        $data['key'] = "min service time";
        $data['value'] = 30; 
        $data['default'] = 30; 
        Setting::create($data);

        $data['key'] = "max service time";
        $data['value'] = 120; 
        $data['default'] = 120;
        Setting::create($data);

        $data['key'] = "time hop for service timing";
        $data['value'] = 30; 
        $data['default'] = 30;
        Setting::create($data);

        $data['key'] = "reminder time before the appointment";
        $data['value'] = 15; 
        $data['default'] = 15;
        Setting::create($data);

        $data['key'] = "book appointment before time";
        $data['value'] = 60; 
        $data['default'] = 60;
        Setting::create($data);

        $data['key'] = "cancel the appointment before time";
        $data['value'] = 30; 
        $data['default'] = 30;
        Setting::create($data);

        $data['key'] = "cost per order";
        $data['value'] = 10; 
        $data['default'] = 10;
        Setting::create($data);

        $data['key'] = "number of reported post for deletion";
        $data['value'] = 15; 
        $data['default'] = 15;
        Setting::create($data);

        $data['key'] = "number of reported and deleted posts to report a salon";
        $data['value'] = 10; 
        $data['default'] = 10;
        Setting::create($data);

        $data['key'] = "handle system suspend";
        $data['value'] = 5; 
        $data['default'] = 5;
        Setting::create($data);

        $data['key'] = "handle ads enabling";
        $data['value'] = 10; 
        $data['default'] = 10;
        
        Setting::create($data);

    }
}

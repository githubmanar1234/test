<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Illuminate\Support\Facades\Cache::flush();
        $jsonString = file_get_contents(base_path('country_dial_info.json'));
        $data = json_decode($jsonString, true);
        // Update Key
        foreach ($data as $country) {
            DB::table('countries')->insert(
                [
                    'name' => $country['name'],
                    'code' => $country['code'],
                    'dial_code' => $country['dial_code'],
                    'flag' => $country['flag']
                ]);
        }
    }
}

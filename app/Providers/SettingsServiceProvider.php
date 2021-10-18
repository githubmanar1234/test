<?php

namespace App\Providers;

use App\Http\Repositories\IRepositories\ISettingRepository;
use App\Models\Setting;
use Illuminate\Contracts\Cache\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * @param ISettingRepository $settingRepository
     */
    public function boot(ISettingRepository $settingRepository)
    {
//        $settings = Setting::all();
//        if ($settings)
//            $settings = $settings->pluck('value', 'key')->toArray();
//        config()->set('settings', $settings);

    }
}

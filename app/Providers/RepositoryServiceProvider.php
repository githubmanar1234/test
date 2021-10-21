<?php

namespace App\Providers;




use App\Http\Repositories\Eloquent\AdminRepository;
use App\Http\Repositories\Eloquent\SalonRepository;
use App\Http\Repositories\Eloquent\AdRepository;
use App\Http\Repositories\Eloquent\CategoryRepository;
use App\Http\Repositories\Eloquent\CityRepository;
use App\Http\Repositories\Eloquent\CountryRepository;
use App\Http\Repositories\Eloquent\JobDetailsRepository;
use App\Http\Repositories\Eloquent\JobRepository;
use App\Http\Repositories\Eloquent\ServiceDetailsRepository;
use App\Http\Repositories\Eloquent\ServiceOrderRepository;
use App\Http\Repositories\Eloquent\ServiceRepository;
use App\Http\Repositories\Eloquent\SettingRepository;
use App\Http\Repositories\Eloquent\SubserviceRepository;
use App\Http\Repositories\Eloquent\SalonReportRepository;
use App\Http\Repositories\Eloquent\UserRepository;
use App\Http\Repositories\IRepositories\IAdminRepository;
use App\Http\Repositories\IRepositories\ISalonRepository;
use App\Http\Repositories\IRepositories\IAdRepository;
use App\Http\Repositories\IRepositories\ICategoryRepository;
use App\Http\Repositories\IRepositories\ICityRepository;
use App\Http\Repositories\IRepositories\ICountryRepository;
use App\Http\Repositories\IRepositories\IJobDetailsRepository;
use App\Http\Repositories\IRepositories\IJobRepository;
use App\Http\Repositories\IRepositories\IServiceDetailsRepository;
use App\Http\Repositories\IRepositories\ISalonReportRepository;
use App\Http\Repositories\IRepositories\IServiceOrderRepository;
use App\Http\Repositories\IRepositories\IServiceRepository;
use App\Http\Repositories\IRepositories\ISettingRepository;
use App\Http\Repositories\IRepositories\ISubserviceRepository;
use App\Http\Repositories\IRepositories\IUserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(IUserRepository::class, UserRepository::class);
        $this->app->bind(IAdminRepository::class, AdminRepository::class);
        $this->app->bind(ISettingRepository::class, SettingRepository::class);
        $this->app->bind(ICountryRepository::class, CountryRepository::class);
        $this->app->bind(ICategoryRepository::class, CategoryRepository::class);
        $this->app->bind(ISalonReportRepository::class, SalonReportRepository::class);
        $this->app->bind(IServiceRepository::class, ServiceRepository::class);
        $this->app->bind(ISalonRepository::class, SalonRepository::class);
        $this->app->bind(IServiceDetailsRepository::class, ServiceDetailsRepository::class);
        $this->app->bind(IJobDetailsRepository::class, JobDetailsRepository::class);
        $this->app->bind(IJobRepository::class, JobRepository::class);
        $this->app->bind(IAdRepository::class, AdRepository::class);
        $this->app->bind(ISubserviceRepository::class, SubserviceRepository::class);
        $this->app->bind(IServiceOrderRepository::class, ServiceOrderRepository::class);
        $this->app->bind(ICityRepository::class, CityRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}

<?php


use App\Http\Controllers\API\Client\CountryController as ClientCountryController;
use App\Http\Controllers\API\Client\SalonController as ClientSalonController;
use App\Http\Controllers\API\Client\UserController as ClientUserController;
use App\Http\Controllers\API\Client\ReportController as ClientReportController;
use App\Http\Controllers\API\Dashboard\Auth\AuthController;
use App\Http\Controllers\API\Dashboard\Auth\PasswordResetController;
use App\Http\Controllers\API\Dashboard\SettingController;
use App\Http\Controllers\API\Dashboard\UserController;
use App\Http\Controllers\API\Dashboard\ReportPostController;
use App\Http\Controllers\API\Dashboard\SalonController;
use App\Http\Controllers\API\Dashboard\CategoryController;
use App\Http\Controllers\API\Dashboard\ReportBarberController;
use App\Http\Controllers\API\Dashboard\ServiceController;
use App\Http\Controllers\API\Dashboard\ReportController;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\API\Client\Auth\AuthController as ClientAuthController;
use \App\Http\Controllers\API\Client\CategoryController as ClientCategoryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


// client routes
Route::middleware('auth:client')->get('getUser', [ClientAuthController::class, "getUser"]);
Route::group([
    "prefix" => "client"
], function () {

    Route::group(["prefix" => "auth"], function () {
        Route::post('login', [ClientAuthController::class, "login"]);
        Route::post('register', [ClientAuthController::class, "register"]);
        Route::group(['middleware' => 'auth:client'], function () {
            Route::get('getUser', [ClientAuthController::class, 'getUser']);
            Route::post('logout', [ClientAuthController::class, 'logout']);
        });
    });
    Route::resource('countries', ClientCountryController::class)->only(['index']);
    Route::get('cities', [ClientCountryController::class, 'cities']);

    Route::group(['middleware' => ['auth:client']], function () {
//        Route::get('cities', function (){
//            return \App\Helpers\JsonResponse::respondSuccess(\App\Helpers\JsonResponse::MSG_SUCCESS, \App\Models\City::all()->pluck('name','id'));
//        });

        Route::get('categories', [ClientCategoryController::class, 'categories']);
        Route::get('categories/find', [ClientCategoryController::class, 'find']);
        Route::get('userInfo', [ClientUserController::class, 'userInfo']);
        Route::get('userById/{user}', [ClientUserController::class, 'userById']);

        Route::post('user/updateInfo', [ClientUserController::class, 'updateInfo']);

        //Salons
        Route::post('createSalon', [ClientSalonController::class, 'store']);
        Route::get('getAcceptedSalons', [ClientSalonController::class, 'getAcceptedSalons']);
        Route::get('getSalonsDeatails', [ClientSalonController::class, 'getSalonsDetails']);
        Route::get('getBarberDetails/{id}', [ClientSalonController::class, 'getBarberDetails']);
        Route::get('salons/find', [ClientSalonController::class, 'find']);

        //Reports
        Route::post('reportBarber', [ClientReportController::class, 'reportBarber']);
        Route::post('reportSalon', [ClientReportController::class, 'reportSalon']);
        Route::post('reportPost', [ClientReportController::class, 'reportPost']);

        
        
   
    });
});


///////////////////////////////// Admin routes ////////////////////////////////////
Route::group([
    "prefix" => "admin"
], function () {
    Route::group(["prefix" => "auth"], function () {
        Route::post('login', [AuthController::class, "login"]);
        Route::post('password/sendResetToken', [PasswordResetController::class, 'sendResetToken']);
        Route::post('password/reset', [PasswordResetController::class, 'reset']);
        Route::group(['middleware' => 'auth:admin'], function () {
            Route::get('getUser', [AuthController::class, 'getUser']);
            Route::post('logout', [AuthController::class, 'logout']);
        });
    });
    Route::group(['middleware' => 'auth:admin'], function () {
        Route::resources([
        ]);
        Route::post('config/flush', function () {
            return \Illuminate\Support\Facades\Cache::flush();
        });
       
        Route::resource('settings', SettingController::class)->only(['index', 'show']);
       
        Route::put('settings/updateKey', [SettingController::class, 'updateKey']);
      
        Route::resource('users', UserController::class)->only(['index', 'show', 'delete']);
      
        Route::get('categories/find', [CategoryController::class, 'find']);
        
        Route::get('getAllServiceCategories', [CategoryController::class, 'index']);
        Route::get('getServiceCategory/{id}', [CategoryController::class, 'show']);
        Route::post('addServiceCategory', [CategoryController::class, 'store']);
        Route::put('editServiceCategory/{id}', [CategoryController::class, 'updateCategory']);
        Route::delete('deleteServiceCategory/{id}', [CategoryController::class, 'destroy']);

        Route::post('updateCategoriesOrder', [CategoryController::class, 'updateCategoriesOrder']);
        Route::get('getCategoriesOrder', [CategoryController::class, 'getCategoriesOrders']);

        //Services
        Route::get('getAllServices', [ServiceController::class, 'index']);
        Route::get('getService/{id}', [ServiceController::class, 'show']);
        Route::get('services/find', [ServiceController::class, 'find']);
        Route::get('serviceOfCategory/find', [ServiceController::class, 'findByCategoryId']);
        Route::post('addService', [ServiceController::class, 'store']);
        Route::put('editService/{id}', [ServiceController::class, 'updateService']);
        Route::delete('deleteService/{id}', [ServiceController::class, 'destroy']);

        //Salon
        Route::get('getAcceptedAndRejectedSalons', [SalonController::class, 'getAcceptedAndRejectedSalons']);
        Route::get('getPendingSalons', [SalonController::class, 'getPendingSalons']);
        Route::post('setAcceptedSalon', [SalonController::class, 'setAcceptedSalon']);
        Route::post('setRejectedSalon', [SalonController::class, 'setRejectedSalon']);
        Route::post('setDisabledSalon', [SalonController::class, 'setDisabledSalon']);
        Route::post('addSalon', [SalonController::class, 'store']);

        //Reported Salons
        Route::get('getReportedSalons', [ReportController::class, 'getReportedSalons']);
        Route::get('getReportBySalon/{id}', [ReportController::class, 'show']);
        Route::post('addReportForSalon', [ReportController::class, 'store']);

        //Reported Posts
        Route::get('getReportedPosts', [ReportPostController::class, 'getReportedPosts']);
        Route::get('getReportByPost/{id}', [ReportPostController::class, 'show']);

        //Reported Barbers
        Route::get('getReportedBarbers', [ReportBarberController::class, 'getReportedBarbers']);
        Route::get('getReportByBarber/{id}', [ReportBarberController::class, 'show']);


        //Settings
        Route::get('getAllSettings', [SettingController::class, 'index']);


    });

});

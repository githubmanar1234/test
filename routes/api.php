<?php


use App\Http\Controllers\API\Client\CountryController as ClientCountryController;
use App\Http\Controllers\API\Client\UserController as ClientUserController;
use App\Http\Controllers\API\Dashboard\Auth\AuthController;
use App\Http\Controllers\API\Dashboard\Auth\PasswordResetController;
use App\Http\Controllers\API\Dashboard\SettingController;
use App\Http\Controllers\API\Dashboard\UserController;
use App\Http\Controllers\API\Dashboard\CategoryController;
use App\Http\Controllers\API\Dashboard\ServiceController;
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
        Route::get('services/find', [CategoryController::class, 'find']);
        Route::post('addService', [ServiceController::class, 'store']);
        Route::put('editService/{id}', [ServiceController::class, 'updateService']);
        Route::delete('deleteService/{id}', [ServiceController::class, 'destroy']);

    });

});

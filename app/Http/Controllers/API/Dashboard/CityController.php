<?php

namespace App\Http\Controllers\API\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Constants;
use App\Helpers\FileHelper;
use App\Helpers\JsonResponse;
use App\Helpers\Mapper;
use App\Models\Category;
use App\Models\Country;
use App\Models\Salon;
use App\Helpers\ValidatorHelper;
use App\Http\Repositories\IRepositories\IServiceRepository;
use App\Http\Repositories\IRepositories\ISalonRepository;
use App\Http\Repositories\IRepositories\ICategoryRepository;
use App\Http\Repositories\IRepositories\IUserRepository;
use App\Http\Repositories\IRepositories\ICityRepository;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CityController extends Controller
{
    private $userRepository;
    private $cityRepository;
    private $serviceRepository;
    private $categoryRepository;
    private $salonRepository;
    private $requestData;
    private $authUser;

    public function __construct(
        IServiceRepository $serviceRepository,
        ICityRepository $cityRepository,
        ICategoryRepository $categoryRepository,
        ISalonRepository $salonRepository,
        IUserRepository $userRepository
    )
    {
        $this->salonRepository = $salonRepository;
        $this->cityRepository = $cityRepository;
        $this->userRepository = $userRepository;
        $this->serviceRepository = $serviceRepository;
        $this->categoryRepository = $categoryRepository;
        $this->requestData = Mapper::toUnderScore(\Request()->all());
        $this->authUser = Auth::guard('admin')->user();
        Validator::extend('languages', function ($attribute, $value, $parameters, $validator) {
            $array_keys = array_keys($value);
            if (count($array_keys) == count(Constants::LANGUAGES)) {
                foreach ($array_keys as $key) {
                    if (!in_array($key, Constants::LANGUAGES))
                        return false;
                }
                return true;
            }
            return false;
        });
    }


    public function storeCity(Request $request)
    {
       
        $data = $this->requestData;
        $validation_rules = [
            'name' => "required",
            'country_id' => "required",  
        ];
        $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
        if ($validator->passes()) {
         
            $country = Country::find($data['country_id']);
            
             if($country){
                $resource = $this->cityRepository->create($data);
        
                if (!$resource) return JsonResponse::respondError(JsonResponse::MSG_CREATION_ERROR);
                return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY), $resource);
             }
             return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
        }
        return JsonResponse::respondError($validator->errors()->all());
    }
  
}

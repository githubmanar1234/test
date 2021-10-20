<?php

namespace App\Http\Controllers\API\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Constants;
use App\Helpers\FileHelper;
use App\Helpers\JsonResponse;
use App\Helpers\Mapper;
use App\Models\Category;
use App\Models\Salon;
use App\Helpers\ValidatorHelper;
use App\Http\Repositories\IRepositories\IServiceRepository;
use App\Http\Repositories\IRepositories\ISalonRepository;
use App\Http\Repositories\IRepositories\ICategoryRepository;
use App\Http\Repositories\IRepositories\IUserRepository;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class SalonController extends Controller
{
    private $userRepository;
    private $serviceRepository;
    private $categoryRepository;
    private $salonRepository;
    private $requestData;
    private $authUser;

    public function __construct(
        IServiceRepository $serviceRepository,
        ICategoryRepository $categoryRepository,
        ISalonRepository $salonRepository,
        IUserRepository $userRepository
    )
    {
        $this->salonRepository = $salonRepository;
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

    public function getPendingSalons()
    {
        $request_data = $this->requestData;

        $data = $this->salonRepository->allAsQuery();

        $data = $data->Where("status", '=', "pending");

        $data = $data->get();
  

        if($data){

            return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
        }
        else{
            return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
        }
        
          
    }

    public function setAcceptedSalon(Request $request)
    {
        $request_data = $this->requestData;
        $validation_rules = [
            'salon_id' => "required",
        ];

        $validator = Validator::make($request_data, $validation_rules, ValidatorHelper::messages());
        if ($validator->passes()) {

            $data = $this->salonRepository->allAsQuery();

            $data = $data->where("id", $request_data['salon_id']);

            $data = $data->first();

            if($data){
                
                if($data->status == "Pending"){

                    $data->status = "Accepted" ;
                    $data->save();
                }
                else{
                    return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
                }
            }
            return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
        }
        return JsonResponse::respondError($validator->errors()->all());
    }

    public function setRejectedSalon(Request $request)
    {
        $request_data = $this->requestData;
        $validation_rules = [
            'salon_id' => "required",
            'reason' => "required",
        ];

        $validator = Validator::make($request_data, $validation_rules, ValidatorHelper::messages());
        if ($validator->passes()) {

            $data = $this->salonRepository->allAsQuery();

            $data = $data->where("id", $request_data['salon_id']);

            $data = $data->first();

            if($data){

                if($data->status == "Pending"){
                $data->status = "Rejected" ;
                $data->reason = $request_data['reason'] ;
                $data->save();
                }     
                else{
                    return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);     
                }
            return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
        }
        return JsonResponse::respondError($validator->errors()->all());
    }
    }

}

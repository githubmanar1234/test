<?php

namespace App\Http\Controllers\API\Client;

use App\Helpers\Constants;
use App\Helpers\JsonResponse;
use App\Helpers\Mapper;
use App\Http\Controllers\Controller;
use App\Http\Repositories\IRepositories\ICategoryRepository;
use App\Http\Repositories\IRepositories\ISalonRepository;
use App\Http\Repositories\IRepositories\IServiceRepository;
use App\Http\Repositories\IRepositories\IUserRepository;
use App\Http\Repositories\IRepositories\IBarberRepository;
use App\Http\Repositories\IRepositories\IBarberReportRepository;
use App\Http\Repositories\IRepositories\ISalonReportRepository;
use App\Http\Repositories\IRepositories\IPostReportRepository;
use App\Http\Repositories\IRepositories\IBarberServiceRepository;
use App\Models\Category;
use App\Models\Salon;
use App\Models\Setting;
use App\Models\BarberService;
use App\Models\Barber;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ValidatorHelper;


class ServiceController extends Controller
{
    private $userRepository;
    private $salonRepository;
    private $barberRepository;
    private $serviceRepository;
    private $barberServicesRepository;
    private $requestData;
    private $authUser;

    public function __construct(
        IUserRepository $userRepository,
        ISalonRepository $salonRepository,
        IBarberRepository $barberRepository,
        IServiceRepository $serviceRepository,
        IBarberServiceRepository $barberServicesRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->barberRepository = $barberRepository;
        $this->serviceRepository = $serviceRepository;
        $this->salonRepository = $salonRepository;
        $this->barberServicesRepository = $barberServicesRepository;
        $this->requestData = Mapper::toUnderScore(\Request()->all());
        $this->authUser = Auth::guard('client')->user();
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

    public function index()
    {
        $data = $this->serviceRepository->all();
      
        if (!$data) return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
        return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
    }

    
    public function show($id)
    {
        $service = Service::find($id);

        if($service){
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_SUCCESS), $service);
        }
        else{
            if (is_numeric($id)){
                return JsonResponse::respondError(JsonResponse::MSG_NOT_FOUND);
            }

            return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
        }  
    }


    
    public function addServicesByBarber(Request $request)
    {
       $user = Auth::guard('barber')->user();

        $data = $this->requestData;
        $validation_rules = [
            'price' => "required",
            'duration' => "required",
            'service_id' => "required|exists:services,id",
        ];
        $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
        if ($validator->passes()) {
                  
                 $barberService = BarberService::where('service_id' , $data['service_id'])->where('barber_id' , $user->id)->first();
                 if(!$barberService){
                        $min = Setting::where('key' , "min service time")->first()->value;
                        $max = Setting::where('key' , "max service time")->first()->value;

                        if($data['duration']  >= $min && $data['duration']  <= $max){
                            
                            $data['barber_id'] = $user->id;
                            // BarberService::where('barber_id' , $user->id )->delete();
                            $resource = $this->barberServicesRepository->create($data);
                        
                            if (!$resource) return JsonResponse::respondError(JsonResponse::MSG_CREATION_ERROR);
                            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY), $resource);
                        }   
                        return JsonResponse::respondError("The duration must be between $min and $max");
                }
                else{

                    $min = Setting::where('key' , "min service time")->first()->value;
                    $max = Setting::where('key' , "max service time")->first()->value;

                    if($data['duration']  >= $min && $data['duration']  <= $max){
                        $barberService->duration = $data['duration'];
                    }
                    else{
                        return JsonResponse::respondError("The duration must be between $min and $max");
                    }

                    $barberService->price = $data['price'] ;
                    $barberService->save();
                    return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY), $barberService);
                }
        }
        return JsonResponse::respondError($validator->errors()->all());
    }

  
}
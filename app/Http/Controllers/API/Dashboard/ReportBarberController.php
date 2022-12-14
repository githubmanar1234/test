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
use App\Http\Repositories\IRepositories\IBarberRepository;
use App\Http\Repositories\IRepositories\ICategoryRepository;
use App\Http\Repositories\IRepositories\IUserRepository;
use App\Http\Repositories\IRepositories\IBarberReportRepository;
use App\Http\Repositories\IRepositories\ISalonReportRepository;
use App\Models\Barber;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class ReportBarberController extends Controller
{
    private $userRepository;
    private $barberRepository;
    private $barberReportRepository;
    private $requestData;
    private $authUser;

    public function __construct(
        IBarberRepository $barberRepository,
        IBarberReportRepository $barberReportRepository,
        IUserRepository $userRepository
        
    )
    {
        $this->barberReportRepository = $barberReportRepository;
        $this->barberRepository = $barberRepository;
        $this->userRepository = $userRepository;
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

   


    //get all reported barbers
    public function getReportedBarbers()
    {
        $request_data = $this->requestData;

        $data = $this->barberRepository->reportedBarbers();
  
        if($data){
            return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
        }
        else{
            return JsonResponse::respondError("There are not reported barbers");
        }
              
    }


    //show report by barber's id
    public function show($id)
    {
        
        // $data =  $this->barberRepository->find($id);
        $data = $this->barberRepository->allAsQuery();
        $data = $data->find($id);
           
        if($data){

            $data = $data->barberReports;
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_SUCCESS), $data);
        }
        else{
            
            if (is_numeric($id)){
                return JsonResponse::respondError(JsonResponse::MSG_NOT_FOUND);
            }

            return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
        }
      
    }

 

  
}

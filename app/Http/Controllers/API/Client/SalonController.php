<?php

namespace App\Http\Controllers\API\Client;

use App\Helpers\Constants;
use App\Helpers\JsonResponse;
use App\Helpers\Mapper;
use App\Http\Controllers\Controller;
use App\Http\Repositories\IRepositories\ICategoryRepository;
use App\Http\Repositories\IRepositories\ISalonRepository;
use App\Http\Repositories\IRepositories\IUserRepository;
use App\Models\Category;
use App\Models\Salon;
use Illuminate\Support\Facades\Auth;


class SalonController extends Controller
{
    private $userRepository;
    private $categoryRepository;
    private $salonRepository;
    private $requestData;
    private $authUser;

    public function __construct(
        ICategoryRepository $categoryRepository,
        IUserRepository $userRepository,
        ISalonRepository $salonRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->categoryRepository = $categoryRepository;
        $this->salonRepository = $salonRepository;
        $this->requestData = Mapper::toUnderScore(\Request()->all());
        $this->authUser = Auth::guard('client')->user();
    }


    public function store(Request $request)
    {
        //Auth ($user_id)
        //ISAvailable and status...

        $data = $this->requestData;
        $validation_rules = [
            'name' => "required",
            'lat_location' => "required",
            'city_id' => "required",
            'salon_code' => "required",
            'type' => "required",
            'location' => "required",
            'long_location' => "required",
            'phone_number' => "required",
            'facebook_link' => "required",
            'whatsapp_number' => "required",    
           
        ];
        $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
        if ($validator->passes()) {

        
            $resource = $this->salonRepository->create($data);
        
            if (!$resource) return JsonResponse::respondError(JsonResponse::MSG_CREATION_ERROR);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY), $resource);
        }
        return JsonResponse::respondError($validator->errors()->all());
    }


    // public function categories(){
    //     $data = $this->categoryRepository->allAsQuery()->get();
    //     return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS,$data);
    // }

   
    // public function find(){
    //     $request_data = $this->requestData;

    //     $data = $this->categoryRepository->allAsQuery();
    //     if (isset($this->requestData['title']))foreach (Constants::LANGUAGES as $LANGUAGE){
    //         $data = $data->orWhere("title->".$LANGUAGE,'like',"%".lcfirst($request_data['title'])."%");
    //         $data = $data->orWhere("title->".$LANGUAGE,'like',"%".ucfirst($request_data['title'])."%");
    //     }
    //     if (isset($this->requestData['description'])) foreach (Constants::LANGUAGES as $LANGUAGE) {
    //         $data = $data->orWhere("description->" . $LANGUAGE, 'like', "%".$request_data['description']."%");
    //     }
    //     $data = $data->get();
    //     return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS,$data);
    // }



}
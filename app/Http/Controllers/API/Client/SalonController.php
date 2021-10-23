<?php

namespace App\Http\Controllers\API\Client;

use App\Helpers\Constants;
use App\Helpers\JsonResponse;
use App\Helpers\Mapper;
use App\Http\Controllers\Controller;
use App\Http\Repositories\IRepositories\ICategoryRepository;
use App\Http\Repositories\IRepositories\ISalonRepository;
use App\Http\Repositories\IRepositories\IUserRepository;
use App\Http\Repositories\IRepositories\IBarberRepository;
use App\Models\Category;
use App\Models\Salon;
use App\Models\Barber;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ValidatorHelper;


class SalonController extends Controller
{
    private $userRepository;
    private $categoryRepository;
    private $salonRepository;
    private $barberRepository;
    private $requestData;
    private $authUser;

    public function __construct(
        ICategoryRepository $categoryRepository,
        IUserRepository $userRepository,
        ISalonRepository $salonRepository,
        IBarberRepository $barberRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->categoryRepository = $categoryRepository;
        $this->barberRepository = $barberRepository;
        $this->salonRepository = $salonRepository;
        $this->requestData = Mapper::toUnderScore(\Request()->all());
        $this->authUser = Auth::guard('client')->user();
    }


    //Create salon by user and create its barberes
    public function store(Request $request)
    {
    
        $user = Auth::guard('client')->user();
    
        $data = $this->requestData;
        $validation_rules = [
            'name' => "required",
            'city_id' => "required",
            'type' => "required",
            'barber_num' => "required",
        ];
        $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
        if ($validator->passes()) {

        
            $salon_code = sprintf("%06d", mt_rand(1, 999999));

            if ($this->isInviteNumberExists($salon_code)) {
                return $this->generateInviteCode();
            }

           $data['salon_code'] = $salon_code;
           $data['user_id'] = $user->id;
           $data['is_available'] = 0;
           $data['is_open'] = 0;

           $resource = $this->salonRepository->create($data);

           $salon_number = intval($data['barber_num']);

            for($i = 0 ; $i<$salon_number ;$i++){

                $barber = [];
           
                $barber['salon_id'] = $resource->id;
                $barber['salon_code']= $resource->salon_code;

                $password = sprintf("%06d", mt_rand(1, 999999));
                           
                $barber['password']= $password;
   
                $barber = $this->barberRepository->create($barber);
            }
          
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


    function isInviteNumberExists($number)
    {
        $salon_code = Salon::where('salon_code', '=', $number)->first();

        if ($salon_code === null )
        {
            return false;
        }
        else
        {
            return true;
        }
    }

  
}
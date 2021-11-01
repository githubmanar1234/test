<?php

namespace App\Http\Controllers\API\Client;

use App\Helpers\Constants;
use App\Helpers\JsonResponse;
use App\Helpers\Mapper;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Http\Repositories\IRepositories\ICategoryRepository;
use App\Http\Repositories\IRepositories\IPostRepository;
use App\Http\Repositories\IRepositories\IUserRepository;
use App\Http\Repositories\IRepositories\ISalonRepository;
use App\Http\Repositories\IRepositories\IPostImageRepository;
use App\Http\Repositories\IRepositories\IPostLikeRepository;
use App\Http\Repositories\IRepositories\IBarberRepository;
use App\Models\Category;
use App\Models\Salon;
use App\Models\Barber;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Image;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ValidatorHelper;


class BarberController extends Controller
{
    private $userRepository;
    private $categoryRepository;
    private $postRepository;
    private $barberRepository;
    private $postImageRepository;
    private $requestData;
    private $authUser;

    public function __construct(
        ICategoryRepository $categoryRepository,
        IUserRepository $userRepository,
        IPostRepository $postRepository,
        ISalonRepository $salonRepository,
        IPostImageRepository $postImageRepository,
        IPostLikeRepository $postLikeRepository,
        IBarberRepository $barberRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->postRepository = $postRepository;
        $this->salonRepository = $salonRepository;
        $this->barberRepository = $barberRepository;
        $this->postLikeRepository = $postLikeRepository;
        $this->postImageRepository = $postImageRepository;
        $this->requestData = Mapper::toUnderScore(\Request()->all());
        //$this->authBarber = Auth::guard('barber')->user();
        $this->authUser = Auth::guard('client')->user();
    }


    public function store(Request $request)
    {
    
        $salon_id = Auth::guard('client')->user()->salon->id;

        $data = $this->requestData;
        $validation_rules = [
            'name' => "required",
            'city_id' => "required",
        ];
      
        $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
        if ($validator->passes()) {

           $salon_code = Auth::guard('client')->user()->salon->salon_code;

           $data['salon_code'] = $salon_code;
           $data['is_available'] = 0;
           $data['salon_id'] = $salon_id;

           $password = sprintf("%06d", mt_rand(1, 999999));
           $data['password']= Hash::make($password);

            $resource = $this->barberRepository->create($data);
        
        if (!$resource) return JsonResponse::respondError(JsonResponse::MSG_CREATION_ERROR);
        return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY), $resource);
        }
        return JsonResponse::respondError($validator->errors()->all());
    }

    public function CompleteBarberInfo(Request $request)
    {

           $user_id = Auth::guard('barber')->user()->id;

            $data = $this->requestData;

            $resource = Barber::find($user_id);
             
            if($resource){
  
            if(isset($data['name'] )){

                $resource->name = $data['name'];
            }
            if(isset($data['phone_number'] )){

                $resource->phone_number = $data['phone_number'];
            }
            if(isset($data['facebook_link'] )){

                $resource->facebook_link = $data['facebook_link'];
            }
            if(isset($data['instagram_link'] )){

                $resource->instagram_link = $data['instagram_link'];
            }
            if(isset($data['whatsapp_number'] )){

                $resource->whatsapp_number = $data['whatsapp_number'];
            }
            $resource->save();
            
            $updated = $this->barberRepository->update($data, $resource->id);
            if (!$updated) return JsonResponse::respondError(trans(JsonResponse::MSG_UPDATE_ERROR));
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
                       
            }
            else{
                return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
            }
      
    }


    //get barbers by salon's id
    public function getBarbersBySalon($id){

        $request_data = $this->requestData;

        $salon = $this->salonRepository->find($id);

        $data = $salon->barbers;
        
        return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
        
    
    }

    //get barbers in same city 
    public function getBarbers(){

        $request_data = $this->requestData;

        $user = Auth::guard('client')->user();

        $salon = $user->salon;

        if($salon){
            
           // $salon_city = $salon->city->name;
           $salon_city_id = $salon->city->id;

           // $data = Barber::where('city' , $salon_city)->get();
           $data = Barber::where('city_id' ,  $salon_city_id )->where('is_availble' , 1)->get();
            
            return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
        }

        return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);  
    }


    //get barber details by barber's id.
    public function getBarberDetails($id){

        $request_data = $this->requestData;

        $data = Barber::find($id);

        if($data){

            return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
        }
        else{
            if (is_numeric($id)){
                return JsonResponse::respondError(JsonResponse::MSG_NOT_FOUND);
            }

            return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
        }  
        
    }

     //Deactivate barber by his salon
    public function deactivateBarber($id)
    {
        $user = Auth::guard('client')->user();

        $salon_id = $user->salon->id;
        
        $resource = Barber::find($id);

        if($resource ){

            if ($resource->salon_id != $salon_id ){
                return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
            }
            
            $resource->is_availble = 0;
            $resource->save();
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        }
        else{
            if (is_numeric($id)){
                return JsonResponse::respondError(JsonResponse::MSG_NOT_FOUND);
            }

            return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
        }   
    }

    function isInviteNumberExists($number)
    {
        $barber_code = Barber::where('salon_code', '=', $number)->first();

        if ($barber_code === null )
        {
            return false;
        }
        else
        {
            return true;
        }
    }
  
}
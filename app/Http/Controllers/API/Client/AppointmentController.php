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
use App\Http\Repositories\IRepositories\IOrderRepository;
use App\Models\Category;
use App\Models\Salon;
use App\Models\Barber;
use App\Models\User;
use App\Models\BarberImage;
use App\Models\Post;
use App\Models\Order;
use App\Models\Timing;
use App\Models\TimingBarber;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Image;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ValidatorHelper;
use App\Helpers\FileHelper;


class AppointmentController extends Controller
{
    private $userRepository;
    private $orderRepository;
    private $barberRepository;
    private $requestData;
    private $authUser;

    public function __construct(
        IUserRepository $userRepository,
        IOrderRepository $orderRepository,
        IBarberRepository $barberRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->orderRepository = $orderRepository;
        $this->barberRepository = $barberRepository;
        $this->requestData = Mapper::toUnderScore(\Request()->all());
        $this->authUser = Auth::guard('client')->user();
    }



    //make appointment/order.
    public function setAppointment(Request $request)
    {
        
        $request_data = $this->requestData;

        $validation_rules = [
            'date' => "required|date",
        ];

        $validator = Validator::make($request_data, $validation_rules, ValidatorHelper::messages());
        if ($validator->passes()) {

            $date =  Carbon::parse($request_data['date'])->dayOfWeekIso;
            
            $date = $this->convert($date);
           

            $timingsSalon =  Timing::where('day' , $date)->get();
            
            if($timingsSalon){

                   $timingsBarber =  TimingBarber::where('day' , $date)->get();

                   if($timingsBarber) {
                         return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $timingsBarber);
                    }          
                   else{
                         return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
                    }
            }
           else{
            return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
           }
        }
        return JsonResponse::respondError($validator->errors()->all());  
    
    }

     //make order. not yet
     public function createOrder(Request $request)
     {
        $user_id = Auth::guard('client')->user()->id;
      
         $data = $this->requestData;
 
         $validation_rules = [
            'start_time' => "required",
            'end_time' => "required",
             'order_number' => "required",
             'barber_id' => "required",
         ];
 
         $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
         if ($validator->passes()) {


            $data['date'] = Carbon::now();
            $data['start_time'] = $data['start_time'];
            $data['end_time'] = $data['end_time'];
            $data['order_number'] = $data['order_number'];
            $data['status'] =  Constants::STATUS_PENDING;
            $data['user_id'] =  $user_id;
            $data['barber_id'] = $data['barber_id'];

            //return $data;
            $resource = $this->orderRepository->create($data);

            if (!$resource) return JsonResponse::respondError(JsonResponse::MSG_CREATION_ERROR);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY), $resource);
           
         }
         return JsonResponse::respondError($validator->errors()->all());  
     
     }

   
     function convert($day){
         
        switch ($day) {

                case('7'):
                    $day = 2;
                    break;

                case('6'):
                    $day = 3;
                    break;    

                case('5'):
                    $day = 4;
                    break; 

                case('4'):
                    $day = 5;
                    break; 
                        
                case('3'):
                    $day = 6;
                    break; 

                case('2'):
                    $day = 7;
                     break; 

                case('1'):
                    $day = 1;
                    break;     


                default:
                $msg = 'Something went wrong.';
        }
        return $day;
     }
}
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
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Image;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ValidatorHelper;
use App\Helpers\FileHelper;


class OrderController extends Controller
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
        $this->authUser = Auth::guard('barber')->user();
    }


    //get daily orders for barbers //time line
    public function getDailyOrders(){

        $request_data = $this->requestData;

        $user = Auth::guard('barber')->user();
        
        if($this->authUser){

            $date = Carbon::now()->format('Y-m-d');
           
            $order = Order::where('date' , $date )->get();
            
             
            if($order){
             
                // $data = Order::where('barber_id' , $user->id )->where('status' , Constants::ORDER_STATUS_ACCEPTED)
                // ->orWhere('status' , Constants::ORDER_STATUS_COMPLETED)->get();
                $data = Order::where('barber_id' , $user->id )->get();
               
                return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
            }
            return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, []);
        }

        return JsonResponse::respondError("You are not barber");  
    }

     //get daily orders for barbers by salon//time line
     public function getDailyOrdersBySalon(){

        $request_data = $this->requestData;

            $date = Carbon::now();
            $order = Order::where('date' , $date )->get();
             
            if($order){
                // $data = Order::where('status' , Constants::ORDER_STATUS_ACCEPTED)
                // ->orWhere('status' , Constants::ORDER_STATUS_COMPLETED)->get();

                $data = Order::all();
          

                return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
            }
            return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, []);
    }

    //get order by id
    public function show($id)
    {
        $order = Order::find($id);

        $data = [];
         
        if($order){
             
            $data['duration'] =  $order->totalDuration();
            $data['order'] =  $order;
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_SUCCESS), $data);
        }
        else{
            if (is_numeric($id)){
                return JsonResponse::respondError(JsonResponse::MSG_NOT_FOUND);
            }

            return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
        }  
    }

    //accept order by barber.
    public function setAcceptedOrder(Request $request)
    {
        
        if($this->authUser){
        $request_data = $this->requestData;
        $validation_rules = [
            'order_id' => "required|exists:orders,id",
        ];

        $validator = Validator::make($request_data, $validation_rules, ValidatorHelper::messages());
        if ($validator->passes()) {

           // $data = $this->orderRepository->allAsQuery();
            $data =  Order::all();
            
            $data = $data->find($request_data['order_id']);

            if($data){

                if($data->status == Constants::ORDER_STATUS_UNDER_REVIEW){
    
                    $data->status = Constants::ORDER_STATUS_ACCEPTED;                 
                    $data->save();
                    return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
                }  
                else{
                    return JsonResponse::respondError("Order is not under review yet");
                    } 
            }
             else{
                return JsonResponse::respondError("Order is not exist");
              }
        }
        return JsonResponse::respondError($validator->errors()->all());
        }

        return JsonResponse::respondError("You are not barber");  
    
    }

    //reject order by barber.
    public function setRejectedOrder(Request $request)
    {
        
        if($this->authUser){
        $request_data = $this->requestData;
        $validation_rules = [
            'order_id' =>"required|exists:orders,id",
            'reject_message' => "required",
        ];

        $validator = Validator::make($request_data, $validation_rules, ValidatorHelper::messages());
        if ($validator->passes()) {

           // $data = $this->orderRepository->allAsQuery();
            $data =  Order::all();
            
            $data = $data->find($request_data['order_id']);

            if($data){

                if($data->status == Constants::ORDER_STATUS_UNDER_REVIEW){
    
                    $data->status = Constants::ORDER_STATUS_REJECTED;  
                    $data->reject_message = $request_data['reject_message'];                 
                    $data->save();
                    return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
                }  
                else{
                    return JsonResponse::respondError("Order is not under review");
                    } 
            }
             else{
                return JsonResponse::respondError("Order is not exist");
              }
        }
        return JsonResponse::respondError($validator->errors()->all());
        }

        return JsonResponse::respondError("You are not barber");  

    }

    //complete order by barber.
    public function setCompletedOrder(Request $request)
    {

        if($this->authUser){
        $request_data = $this->requestData;
        $validation_rules = [
            'order_id' => "required|exists:orders,id",
        ];

        $validator = Validator::make($request_data, $validation_rules, ValidatorHelper::messages());
        if ($validator->passes()) {

           // $data = $this->orderRepository->allAsQuery();
            $data =  Order::all();
            
            $data = $data->find($request_data['order_id']);

            if($data){

                if($data->status == Constants::ORDER_STATUS_ACCEPTED){
    
                    $data->status = Constants::ORDER_STATUS_COMPLETED;                   
                    $data->save();
                    return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
                }  
                else{
                    return JsonResponse::respondError("Order not accepted yet");
                    } 
            }
             else{
                return JsonResponse::respondError("Order is not exist");
              }
        }
        return JsonResponse::respondError($validator->errors()->all());
        }

        return JsonResponse::respondError("You are not barber"); 

    }

     //incomplete order by barber.
     public function setInCompletedOrder(Request $request)
     {
 
         if($this->authUser){
         $request_data = $this->requestData;
         $validation_rules = [
             'order_id' => "required|exists:orders,id",
         ];
 
         $validator = Validator::make($request_data, $validation_rules, ValidatorHelper::messages());
         if ($validator->passes()) {
 
            // $data = $this->orderRepository->allAsQuery();
             $data =  Order::all();
             
             $data = $data->find($request_data['order_id']);
 
             if($data){
 
                 if($data->status == Constants::ORDER_STATUS_ACCEPTED){
     
                     $data->status = Constants::ORDER_STATUS_INCOMPLETED;                   
                     $data->save();
                     return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
                 }  
                 else{
                    return JsonResponse::respondError("Order not accepted yet");
                     } 
             }
              else{
                 return JsonResponse::respondError("Order is not exist");
               }
         }
         return JsonResponse::respondError($validator->errors()->all());
         }
 
         return JsonResponse::respondError("You are not barber");  
 
     }

      //cancel order by user.
    public function setCanceledOrder(Request $request)
    {
        
        $user = Auth::guard('client')->user();
       
        if($user){
            $request_data = $this->requestData;
            $validation_rules = [
                'order_id' => "required|exists:orders,id",
                'cancel_message' => "",
            ];

            $validator = Validator::make($request_data, $validation_rules, ValidatorHelper::messages());
            if ($validator->passes()) {

                $data =  Order::all();

                $data = $data->find($request_data['order_id']);
                
                $start_time = Carbon::parse($data->start_time);

                $now = Carbon::now()->format('Y-m-d H:i:s');
              
                $restTime = $start_time->diffInHours($now);

                //not after end the order
                $value = Setting::where('key' , "cancel the appointment before time")->first()->value;

                        if($restTime <= $value){
                            if($data->status == Constants::ORDER_STATUS_UNDER_REVIEW || $data->status == Constants::ORDER_STATUS_ACCEPTED){
                        
                                $data->status = Constants::ORDER_STATUS_CANCELED; 
                                
                                if(isset($request_data['cancel_message'])) {

                                    $data->reject_message = $request_data['cancel_message']; 
                                }
                                               
                                $data->save();
                                return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
                            }  
                            else{
                                return JsonResponse::respondError("This order not accepted or under review now");
                            } 
                        }
                        else{
                            return JsonResponse::respondError("You can not cancel this order now");
                        }
            }
            return JsonResponse::respondError($validator->errors()->all());
        }

        return JsonResponse::respondError("You are not user");  
    
    }

     //under review order by barber.
     public function setUnderReviewOrder(Request $request)
     {
 
         if($this->authUser){
         $request_data = $this->requestData;
         $validation_rules = [
             'order_id' => "required|exists:orders,id",
         ];
 
         $validator = Validator::make($request_data, $validation_rules, ValidatorHelper::messages());
         if ($validator->passes()) {
 
            // $data = $this->orderRepository->allAsQuery();
             $data =  Order::all();
             
             $data = $data->find($request_data['order_id']);
 
             if($data){
 
                 if($data->status == Constants::ORDER_STATUS_ACCEPTED){
     
                     $data->status = Constants::ORDER_STATUS_UNDER_REVIEW;                   
                     $data->save();
                     return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
                 }  
                 else{
                     return JsonResponse::respondError("Order not accepted yet");
                     } 
             }
              else{
                 return JsonResponse::respondError("Order is not exist");
               }
         }
         return JsonResponse::respondError($validator->errors()->all());
         }
 
         return JsonResponse::respondError("You are not barber"); 
 
     }

    
    //view user profile by his barber.
     public function profileUser($id)
    {
        
        if($this->authUser){
        
            $data = Order::where('user_id' ,$id)->where('barber_id' ,$this->authUser->id )
            ->where('status' ,Constants::ORDER_STATUS_ACCEPTED )->first();
            
            if($data){

                return JsonResponse::respondSuccess(trans(JsonResponse::MSG_SUCCESS), $data);
            }
            else{
                if (is_numeric($id)){
                    return JsonResponse::respondError(JsonResponse::MSG_NOT_FOUND);
                }

                return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
            }  
        }
        else{
            return JsonResponse::respondError("You are not barber");
        }
        
    }

    //get orders for users
    public function getOrdersUser(){

        $request_data = $this->requestData;

        $user = Auth::guard('client')->user();
       
       
        if($user){

            $data = Order::where('user_id' , $user->id)->get();
            return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
        }

        return JsonResponse::respondError("You are not user");  
    }

    //rate order by user.
    public function rateOrders(Request $request)
    {
        $request_data = $this->requestData;
        
        $user = Auth::guard('client')->user();
      
        if($user){

            $request_data = $this->requestData;
            $validation_rules = [
                'order_id' => "required|exists:orders,id",
                'rate' => "required|numeric",
            ];

            $validator = Validator::make($request_data, $validation_rules, ValidatorHelper::messages());
            if ($validator->passes()) {

                $data =  Order::where('id' , $request_data['order_id'])->where('user_id' , $user->id)->first();
               
                if($data){

                    if($data->status == Constants::ORDER_STATUS_COMPLETED){
        
                        $data->rate = $request_data['rate'];                   
                        $data->save();
                        return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
                    }  
                    else{
                        return JsonResponse::respondError("This order isnt completed");
                        } 
                }
                else{
                    return JsonResponse::respondError("You dont take this order");
                }
            }
            return JsonResponse::respondError($validator->errors()->all());
        }

        return JsonResponse::respondError("You are not user");  

    }

     //write review for order by user.
     public function writeReviewForOrder(Request $request)
     {
         $request_data = $this->requestData;
         
         $user = Auth::guard('client')->user();
        
         if($user){
 
                $request_data = $this->requestData;
                $validation_rules = [
                    'order_id' => "required|exists:orders,id",
                    'notes' => "required",
                ];
        
                $validator = Validator::make($request_data, $validation_rules, ValidatorHelper::messages());
                if ($validator->passes()) {
        
                    $data =  Order::where('id' , $request_data['order_id'])->where('user_id' , $user->id)->first();
                    
                    if($data){
        
                        if($data->status == Constants::ORDER_STATUS_COMPLETED){
            
                            $data->notes = $request_data['notes'];                   
                            $data->save();
                            return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
                        }  
                        else{
                            return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
                            } 
                    }
                    else{
                        return JsonResponse::respondError("You dont have this order");
                    }
                }
                return JsonResponse::respondError($validator->errors()->all());
         }
 
         return JsonResponse::respondError("You are not user", ResponseStatus::NOT_AUTHORIZED);  
 
     }


    public function findAsSalon()
    {

        $userClient = Auth::guard('client')->user();
        if(!$userClient){
            return JsonResponse::respondError("not authenticate");
        }
        $salon_id = Auth::guard('client')->user()->salon_id;
        // $userBarber = Auth::guard('barber')->user();

        $request_data = $this->requestData;

        $data = $this->orderRepository->allAsQuery();

        $data = $data->Where("status", Constants::STATUS_ACCEPTED);

        $data = $data->whereHas('barber', function ($q) use ($salon_id) {
            $q->where('salon_id', $salon_id );
        });
        
        if($userClient){
            if($userClient->role == "salon"){
 
                if (isset($this->requestData['barber_id'])){
                    $data->where("barber_id", "=" , $request_data['barber_id']);
                }

                if (isset($this->requestData['order_number'])){
                    $data->where("order_number", "=" , $request_data['order_number']);
                }

                if (isset($this->requestData['date'])){
                    $data->where("date", "=" , $request_data['date']);
                }

            }
            else{
                return JsonResponse::respondError("You are not a salon");
            }

        }
       
        $data = $data ->get();
        return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS,$data);
     
    }

    public function findAsBarber()
    {

        $userBarber = Auth::guard('barber')->user();
        if(!$userBarber){
            return JsonResponse::respondError("not authenticate");
        }
       
        $barber_id = Auth::guard('barber')->user()->id;

        $request_data = $this->requestData;

        $data = $this->orderRepository->allAsQuery();

        $data = $data->Where("status", Constants::STATUS_ACCEPTED)->where('barber_id' ,$barber_id);
        
        if($userBarber){

                if (isset($this->requestData['order_number'])){
                    $data->where("order_number", "=" , $request_data['order_number']);
                }

                if (isset($this->requestData['date'])){
                    $data->where("date", "=" , $request_data['date']);
                }
        
        }
        $data = $data ->get();
        return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS,$data);
     
    }

   
}
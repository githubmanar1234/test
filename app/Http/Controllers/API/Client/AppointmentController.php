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
            'day' => "required|numeric",
        ];

        $validator = Validator::make($request_data, $validation_rules, ValidatorHelper::messages());
        if ($validator->passes()) {

            $timingSalon =  Timing::where('day' , $request_data['day'])->get();
            
            $data = $data->find($request_data['order_id']);

            if($data){

                if($data->status == Constants::ORDER_STATUS_UNDER_REVIEW){
    
                    $data->status = Constants::ORDER_STATUS_ACCEPTED;                 
                    $data->save();
                    return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
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

    //reject order by barber.
    public function setRejectedOrder(Request $request)
    {

        
        if($this->authUser){
        $request_data = $this->requestData;
        $validation_rules = [
            'order_id' => "required",
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
                    return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
                    } 
            }
             else{
                return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
              }
        }
        return JsonResponse::respondError($validator->errors()->all());
        }

        return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);  

    }

    //complete order by barber.
    public function setCompletedOrder(Request $request)
    {

        if($this->authUser){
        $request_data = $this->requestData;
        $validation_rules = [
            'order_id' => "required",
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
                    return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
                    } 
            }
             else{
                return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
              }
        }
        return JsonResponse::respondError($validator->errors()->all());
        }

        return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);  

    }

     //incomplete order by barber.
     public function setInCompletedOrder(Request $request)
     {
 
         if($this->authUser){
         $request_data = $this->requestData;
         $validation_rules = [
             'order_id' => "required",
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
                     return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
                     } 
             }
              else{
                 return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
               }
         }
         return JsonResponse::respondError($validator->errors()->all());
         }
 
         return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);  
 
     }

      //cancel order by barber.
    public function setCanceledOrder(Request $request)
    {
        
        if($this->authUser){
        $request_data = $this->requestData;
        $validation_rules = [
            'order_id' => "required",
        ];

        $validator = Validator::make($request_data, $validation_rules, ValidatorHelper::messages());
        if ($validator->passes()) {

           // $data = $this->orderRepository->allAsQuery();
            $data =  Order::all();
            
            $data = $data->find($request_data['order_id']);

            if($data){

                if($data->status == Constants::ORDER_STATUS_UNDER_REVIEW || $data->status == Constants::ORDER_STATUS_ACCEPTED){
    
                    $data->status = Constants::ORDER_STATUS_CANCELED;                 
                    $data->save();
                    return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
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

        return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);  
    
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
    }

    //get orders for users
    public function getOrdersUser(){

        $request_data = $this->requestData;

        $user = Auth::guard('client')->user();
       
        if($user){

            $data = Order::where('user_id' , $user->id)->get();
            return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
        }

        return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);  
    }

    //rate order by user.
    public function rateOrders(Request $request)
    {
        $request_data = $this->requestData;
        
        $user = Auth::guard('client')->user();
       
        if($user){

        $request_data = $this->requestData;
        $validation_rules = [
            'order_id' => "required",
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
                    return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
                    } 
            }
             else{
                return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
              }
        }
        return JsonResponse::respondError($validator->errors()->all());
        }

        return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);  

    }

     //write review for order by user.
     public function writeReviewForOrder(Request $request)
     {
         $request_data = $this->requestData;
         
         $user = Auth::guard('client')->user();
        
         if($user){
 
         $request_data = $this->requestData;
         $validation_rules = [
             'order_id' => "required",
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
                 return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
               }
         }
         return JsonResponse::respondError($validator->errors()->all());
         }
 
         return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);  
 
     }

   
}
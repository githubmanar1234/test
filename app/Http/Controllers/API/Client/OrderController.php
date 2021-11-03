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


    //get daily orders for barbers
    public function getOrders(){

        $request_data = $this->requestData;

         $user = Auth::guard('barber')->user();
        //$name = Auth::guard('barber')->user()->name;
        
        
        if($this->authUser){

            $data = Order::where('barber_id' , $user->id )->where('status' , Constants::ORDER_STATUS_ACCEPTED)
            ->orWhere('status' , Constants::ORDER_STATUS_COMPLETED)->get();
            $data['barber'] = $user;
           // $data['barberName'] = $name;
            return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
        }

        return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);  
    }

    //accept order by barber.
    public function setAcceptedOrder(Request $request)
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

    return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);  
    
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
    

     

}
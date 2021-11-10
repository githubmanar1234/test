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
use App\Models\OrderService;
use App\Models\Salon;
use App\Models\BarberService;
use App\Models\Barber;
use App\Models\Service;
use App\Models\User;
use App\Models\BarberImage;
use App\Models\Post;
use App\Models\Order;
use App\Models\Timing;
use App\Models\TimingBarber;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Image;
use DateTime;
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


    public function setAppointment(Request $request)
    {
        
        $request_data = $this->requestData;
        $validation_rules = [            
            'date' => "required|date",
            'salon_id' => "required|exists:salons,id",
            'barber_id' => "required|exists:barbers,id",
            'barber_services' => 'required',         
        ];

        $validator = Validator::make($request_data, $validation_rules, ValidatorHelper::messages());
        if ($validator->passes()) {

            $barberServices = $request_data['barber_services'];
            $barberServices = json_decode($barberServices, true);
            
            $duration = 0;

            if (is_array($barberServices)) {
                if (isset($barberServices[0])) {
                     
                    foreach ($barberServices as $serviceId) {
                       
                        $barberService = BarberService::find($serviceId);
                        
                        if($barberService){
                          
                            if($barberService->barber_id == $request_data['barber_id']){
                             
                                $duration +=  $barberService->duration;
                                
                            }
                            else{
                                return JsonResponse::respondError("Barber don't do this service");
                            }
                        }
                        else{
                            return JsonResponse::respondError("Service not found");
                        }
                        
                    }
                }
            }
            else{return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);}
            
            if($duration == 0){
                return JsonResponse::respondError("Your duration is");
            }

            $day =  Carbon::parse($request_data['date'])->dayOfWeekIso;
            
            $day = $this->convert($day);
          

             //If salon accepted and avilable .
            $salonIsAccepted = Salon::where('id' , $request_data['salon_id'])->where('status' , Constants::STATUS_ACCEPTED)
            ->where('is_available' , 1)->first();
             
            if(!$salonIsAccepted){
                return JsonResponse::respondError("This salon is not available");
            }

            //To sure if the salon is open  in this day.
            $timingsSalon =  Timing::where('day' , $day)->where('salon_id', $request_data['salon_id'])->get();
            if(count($timingsSalon) == 0){
                return JsonResponse::respondError("This salon is not open");
            }
            
            //To sure if the barber is open  in this day.
            $timingsBarber = TimingBarber::where('day' , $day)->where('barber_id', $request_data['barber_id'])->get();
            if(count($timingsBarber) == 0){
                return JsonResponse::respondError("This barber is not available");
            }
            $orders = Order::whereDate('date' ,$request_data['date'])->where('barber_id', $request_data['barber_id'])->get();
             
            return $this->avilableTimes($day , $timingsBarber ,$orders, $duration);
           
        }
    
            return JsonResponse::respondError($validator->errors()->all()); 
    }

     //make order. 
     public function createOrder(Request $request)
    {
        $user_id = Auth::guard('client')->user()->id;
      
         $data = $this->requestData;
         $validation_rules = [
             'start_time' => "required",
             'end_time' => "required",
             'date' => "date|required",
             'barber_id' => "required",
             'barber_services' => 'required',
         ];
 
         $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
         if ($validator->passes()) {

            $order_number = sprintf("%06d", mt_rand(1, 999999));
        
            if ($this->isOrderNumberExists($order_number)) {
                 $order_number = $this->generateInviteCode();
            }
        
            
            if (Carbon::parse($data["date"])->isToday()){   
                
                $value = Setting::where('key' , "book appointment before time")->first()->value;
    
                $startTime = Carbon::parse($data['start_time']);

                $time = $startTime->diffInMinutes(Carbon::now()); 
               
                if($time < $value){
                    return JsonResponse::respondError("You can't create order now");
                }
            }

            $data['status'] =  Constants::STATUS_PENDING;
            $data['user_id'] =  $user_id;
            $data['order_number'] =  $order_number;

            $barberServices = $data['barber_services'];
            $barberServices = json_decode($barberServices, true);
            $duration = 0;
            if (is_array($barberServices)) {
                if (isset($barberServices[0])) {
                    
                    foreach ($barberServices as $serviceId) {
                       
                        $barberService = BarberService::find($serviceId);
                        
                        if($barberService){
                            
                            if($barberService->barber_id == $data['barber_id']){

                                    $duration +=  $barberService->duration;
                                
                            }
                            else{
                                return JsonResponse::respondError("Barber don't do this service");
                            }
                        }
                        else{
                            return JsonResponse::respondError("Service not found");
                        }
                        
                    }
                }
            }

            $barber = Barber::where('id' ,$data['barber_id'])->first();
            $salon_id =  $barber->salon->id;
            //If salon accepted and avilable .
            $salonIsAccepted = Salon::where('id' , $salon_id)->where('status' , Constants::STATUS_ACCEPTED)
            ->where('is_available' , 1)->first();
            if(!$salonIsAccepted){
                return JsonResponse::respondError("This salon is not available");
            }
                
            //to check if start and end time in avilable times for barber.
            $day =  Carbon::parse($data['date'])->dayOfWeekIso;
            $day = $this->convert($day);
             
            $timingsBarber = TimingBarber::where('day' , $day)->where('barber_id', $data['barber_id'])->get();
            $orders = Order::whereDate('date' , $data['date'])->where('barber_id', $data['barber_id'])->get();
            
            if(count($timingsBarber) == 0){
                return JsonResponse::respondError("Barber does not exist in this day");
            }
           
            $startOrderTime = Carbon::createFromFormat('H:i:s', $data['start_time']);
            $endOrderTime = Carbon::createFromFormat('H:i:s', $data['end_time']);

            $availableTimes = $this->avilableTimes($day , $timingsBarber ,$orders, $duration);
            
            foreach($availableTimes as $availableTime){
                
                $availableStartTime = Carbon::createFromFormat('H:i:s',$availableTime['slot_start_time']);
                $endOrderEndTime = Carbon::createFromFormat('H:i:s', $availableTime['slot_end_time']);

                if($startOrderTime->eq($availableStartTime) && $endOrderEndTime->eq($endOrderTime)){
                
                    $resource = $this->orderRepository->create($data);
                    if (!$resource) return JsonResponse::respondError(JsonResponse::MSG_CREATION_ERROR);

                    
                    if (is_array($barberServices)) {
                        if (isset($barberServices[0])) {
                            
                            foreach ($barberServices as $serviceId) {
                            
                                $barberService = BarberService::find($serviceId);
                                
                                if($barberService){
                                
                                    if($barberService->barber_id == $data['barber_id']){
        
                                            $orderService = new OrderService();
                                            $orderService->order_id  = $resource->id;
                                            $orderService->bareber_services_id = $barberService->id;
                                            $orderService->save();
                                        
                                    }
                                    else{
                                        return JsonResponse::respondError("Barber don't do this service");
                                    }
                                }
                                else{
                                    return JsonResponse::respondError("Service not found");
                                }
                                
                            }
                        }
                    }
                    else{return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);}

                    $resource->orderServices;
                    $resource->user;
                    $resource->barber;
                    return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY), $resource);
                }
            } 
            
            return JsonResponse::respondError("There is not time for your order");  
            
         }
         return JsonResponse::respondError($validator->errors()->all());  
    }


    //get available times.
    function avilableTimes($day , $timingsBarber ,$orders , $duration){

        foreach($timingsBarber as $timingBarber){

            $start = Carbon::createFromFormat('H:i:s', $timingBarber->from);
            $end = Carbon::createFromFormat('H:i:s', $timingBarber->to);

            $startTime = $start->format('H:i:s');
            $endTime = $end->format('H:i:s');
            $i=0;
            $time = [];
           

            while(strtotime($startTime) <= strtotime($endTime)) {
                $start = $startTime;
                $end = date('H:i:s',strtotime('+'.$duration.' minutes',strtotime($startTime)));

                $startTime = date('H:i:s',strtotime('+'.$duration.' minutes',strtotime($startTime)));

                $i++;

                if(strtotime($startTime) <= strtotime($endTime)){

                    $isVaild = true;

                    
                    $startTie = Carbon::createFromFormat('H:i:s', $start);
                    $endTie = Carbon::createFromFormat('H:i:s',  $end);


                  //  $orders = Order::whereDate('date' ,$request_data['date'])->where('barber_id', $request_data['barber_id'])->get();
                  
                    foreach ($orders as $key => $order) {
                        $fromOrderTime = Carbon::createFromFormat('H:i:s', $order->start_time);
                        $toOrderTime = Carbon::createFromFormat('H:i:s', $order->end_time);

                        if ($startTie->gte($fromOrderTime) && $toOrderTime->gte($endTie)){
                            $isVaild = false;
                            break;
                        }

                    }

                    if ($isVaild) {
                     
                        $time[$i]['slot_start_time'] = $start;
                        $time[$i]['slot_end_time'] = $end;
                    }
                }
            }
           return $time;        
        }

    }
   
     function convert($day){
         
        switch ($day) {

                case('7'):
                    $day = 2;
                    break;

                case('6'):
                    $day = 1;
                    break;    

                case('5'):
                    $day = 7;
                    break; 

                case('4'):
                    $day = 6;
                    break; 
                        
                case('3'):
                    $day = 5;
                    break; 

                case('2'):
                    $day = 4;
                     break; 

                case('1'):
                    $day = 3;
                    break;     


                default:
                $msg = 'Something went wrong.';
        }
        return $day;
     }

     function isOrderNumberExists($number)
     {
         $order_number = Order::where('order_number', '=', $number)->first();
 
         if ($order_number === null )
         {
             return false;
         }
         else
         {
             return true;
         }
     }
}
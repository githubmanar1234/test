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
use App\Http\Repositories\IRepositories\ITimingBarberRepository;
use App\Http\Repositories\IRepositories\IPostLikeRepository;
use App\Http\Repositories\IRepositories\IBarberRepository;
use App\Models\Category;
use App\Models\Salon;
use App\Models\Timing;
use App\Models\Block;
use App\Models\User;
use App\Models\Order;
use App\Models\TimingBarber;
use App\Models\Barber;
use App\Models\BarberImage;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Image;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ValidatorHelper;
use App\Helpers\FileHelper;


class BarberController extends Controller
{
    private $userRepository;
    private $categoryRepository;
    private $postRepository;
    private $barberRepository;
    private $timingBarberRepository;
    private $requestData;
    private $authUser;

    public function __construct(
        ICategoryRepository $categoryRepository,
        IUserRepository $userRepository,
        IPostRepository $postRepository,
        ISalonRepository $salonRepository,
        ITimingBarberRepository $timingBarberRepository,
        IPostLikeRepository $postLikeRepository,
        IBarberRepository $barberRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->postRepository = $postRepository;
        $this->salonRepository = $salonRepository;
        $this->barberRepository = $barberRepository;
        $this->postLikeRepository = $postLikeRepository;
        $this->timingBarberRepository = $timingBarberRepository;
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

          // $salon_code = Auth::guard('client')->user()->salon->salon_code;

           $barber_code = sprintf("%06d", mt_rand(1, 999999));
                       
           $data['barber_code']= $barber_code;

          // $data['salon_code'] = $salon_code;
           $data['is_available'] = 0;
           $data['salon_id'] = $salon_id;

           $password = sprintf("%06d", mt_rand(1, 999999));
           $data['password']= $password;
           $data['status']= Constants::STATUS_PENDING;

            $resource = $this->barberRepository->create($data);
        
        if (!$resource) return JsonResponse::respondError(JsonResponse::MSG_CREATION_ERROR);
        return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY), $resource);
        }
        return JsonResponse::respondError($validator->errors()->all());
    }

    //complete barber profile with his timeLines.
    public function CompleteBarberInfo(Request $request)
    {

           $user_id = Auth::guard('barber')->user()->id;
           $salon_id = Auth::guard('barber')->user()->salon->id;
           
           $data = $this->requestData;

            $validation_rules = [
                'days' => 'required',
                'from' => 'required',
                'to' => 'required',
            ];
            $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
            if ($validator->passes()) {

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

                $resource->status = Constants::STATUS_ACCEPTED;
                $resource->save();

                
                $days = $data['days'];
                $days = json_decode($days, true);
                
                $from = $data['from'];
                $from = json_decode($from, true);
        
                $to = $data['to'];
                $to = json_decode($to, true);

                
                
                if (is_array($days)) {
                    if (isset($days[0])) {
                        
                        TimingBarber::where('barber_id' ,$resource->id)->delete();

                        foreach ($days as $key => $day) {
                            if($day > 0 && $day < 8){
                            $timingsSalonInday =  Timing::where('salon_id', $salon_id)->where('day', $day)->get();

                            if(count($timingsSalonInday) > 0){
                                $timingBarber = new TimingBarber();
                                $timingBarber->barber_id = $resource->id;
                                $timingBarber->day = $day;


                                $fromVaild = false;

                                $fromTime = Carbon::createFromFormat('H:i', $from[$key]);
                                $toTime = Carbon::createFromFormat('H:i', $to[$key]);
                                                    
                                // from < to
                                if($fromTime->gt($toTime)) {
                                    return "wrong times" ;
                                }

                                foreach ($timingsSalonInday as $key => $timingSalon) {
                                    $fromSlonTime = Carbon::createFromFormat('H:i:s', $timingSalon->from);
                                    $toSalonTime = Carbon::createFromFormat('H:i:s', $timingSalon->to);
                                   
                                    if ($fromTime->gte($fromSlonTime) && $toSalonTime->gte($toTime)){
                                        $fromVaild = true;
                                        break;
                                    }
                                }

                                $timingBarber->from = $fromTime;
                                $timingBarber->to =  $toTime;
                                if ($fromVaild){
                                    $timingBarber->save();
                                }
                                else{
                                    return "wrong times" ;
                                }
                            }
                        }
                        else{
                            return JsonResponse::respondError("your days must be between 1 and 7");
                        }
                    } 
                    
                    }
                }

                if ( $request->hasfile('images')) {

                    $files = $request->file('images'); 
                    BarberImage::where('barber_id' ,$resource->id)->delete();

                    foreach ($files as $file) {      
                        $imageUrl = FileHelper::processImage($file, 'public/barbers');
                        $barberImage = new BarberImage();
                        $barberImage->image = $imageUrl;
                        $barberImage->barber_id  = $resource->id;
                        $barberImage->save();
                    
                    }
                }

                return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
                       
            }
            else{
                return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
            }
        }
        return JsonResponse::respondError($validator->errors()->all());
    }


    //get barbers by salon's id
    public function getBarbersBySalon($id){

        $salon = $this->salonRepository->find($id);

        $data = $salon->barbers;
  
        return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
   
    }


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
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_SUCCESS));
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


    //get barber timelines by user.
    public function getBarberTimeLinesByUser($id){

        $request_data = $this->requestData;

        $user = Auth::guard('client')->user();

        $data = Barber::find($id);
        if($user->role == "user"){

            $block = Block::where('barber_id' , $id)->where('user_id' ,$user->id)->first();
            if(!$block){
                if($data){

                    $timingesBarber =  TimingBarber::where('barber_id' , $id)->get();
                        return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $timingesBarber);
                }
                else{
                    if (is_numeric($id)){
                        return JsonResponse::respondError(JsonResponse::MSG_NOT_FOUND);
                    }

                    return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
                    }  
            }
            else{
                return JsonResponse::respondError("You can not see this barber's detailes");
            }
            
        }
    }

    //get barber timelines by salon.
    public function getBarberTimeLinesBySalon($id){

        $request_data = $this->requestData;

        $user = Auth::guard('client')->user();
        $salon_id = $user->salon->id;
    
        $data = Barber::find($id);

        if($data){
            $barber = Barber::where('id' ,$id)->where('salon_id' , $salon_id)->first();
            if($barber){
                $timingesBarber =  TimingBarber::where('barber_id' , $id)->get();
                return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $timingesBarber);
            }
            else{
                return JsonResponse::respondError("You can not see this barber's detailes");
            }
            
        }
        else{
             if (is_numeric($id)){
              return JsonResponse::respondError(JsonResponse::MSG_NOT_FOUND);
            }
           return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
        }        
        
    }

    //get barbers in same city 
    public function getBarbers(){

        $request_data = $this->requestData;

        $user = Auth::guard('client')->user();
    
        $salon = $user->salon;

        if($user->role == "salon"){
            if($salon){
            $salon_city_id = $salon->city->id;
            $data = Barber::where('city_id' ,  $salon_city_id )->where('is_availble' , 1)->get();
            return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
            }
            return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);  
        }
        else{
            if($user->role == "user"){

                $barbers = Barber::where('is_availble' , 1)->get();
                foreach($barbers as  $barber){

                    $block = Block::where('barber_id' , $barber->id)->where('user_id' ,$user->id)->first();
                   
                    $barbersNotBlock= Barber::where('is_availble' , 1)->where('id' ,'!=',$barber->id)->get();
                    if($block){
                        return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $barbersNotBlock);
                    }
                    else{
                        return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $barbers);
                    }
                   
                }
               
            }
        }
    }
    
    //get barber details by barber's id.
    public function getBarberDetails($id){

        $request_data = $this->requestData;

        $user = Auth::guard('client')->user();

        $data = Barber::find($id);

        if($user->role == "salon"){
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
        else{
            if($user->role == "user"){

                    $block = Block::where('barber_id' , $data->id)->where('user_id' ,$user->id)->first();
                    if($data){
                        if(!$block){
                            return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
                        }
                        else{
                            return JsonResponse::respondError("You can not see this barber's detailes");
                        }
                    }
                   
                    else{
                        if (is_numeric($id)){
                            return JsonResponse::respondError(JsonResponse::MSG_NOT_FOUND);
                        }
        
                        return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
                    } 
               
            }
        }
    }
    
     //Block user by his barber
     public function blockUser()
     {
         $barber = Auth::guard('barber')->user();
 
         $data = $this->requestData;
 
         $validation_rules = [
             'user_id' => 'required|exists:users,id',
         ];
         $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
         if ($validator->passes()) {

              $role = User::where('id' , $data['user_id'])->first()->role;
            
              $order = Order::where('user_id', $data['user_id'])->where('barber_id', $barber->id)->first();
               if($role !== "salon"){
                    if($order){
                        $block = new Block();
                        $block->user_id  =  $data['user_id'];
                        $block->barber_id = $barber->id;
                        $block->save();
                        return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $block);
                    }
                    else{
                        return JsonResponse::respondError("You can not block this user");
                    }
                }
                else{
                    return JsonResponse::respondError("You can not block salon");
                }
            
         }
         return JsonResponse::respondError($validator->errors()->all());
     }
}
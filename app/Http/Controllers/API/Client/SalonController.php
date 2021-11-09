<?php

namespace App\Http\Controllers\API\Client;

use App\Helpers\Constants;
use App\Helpers\JsonResponse;
use Illuminate\Support\Facades\Hash;
use App\Helpers\FileHelper;
use App\Helpers\Mapper;
use App\Http\Controllers\Controller;
use App\Http\Repositories\IRepositories\ICategoryRepository;
use App\Http\Repositories\IRepositories\ISalonRepository;
use App\Http\Repositories\IRepositories\IUserRepository;
use App\Http\Repositories\IRepositories\IBarberRepository;
use App\Models\Category;
use App\Models\Timing;
use App\Models\Salon;
use App\Models\Barber;
use Carbon\Carbon;
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


    //Create salon by user 
    public function RegisterSalon(Request $request)
    {
    
        $user = Auth::guard('client')->user();

        $salon = Auth::guard('client')->user()->salon;
        
        $data = $this->requestData;

        if(!$salon){
            
                $validation_rules = [
                    'name' => "required",
                    'city_id' => "required",
                    'type' => "required",
                    'berbers_num' => "required",
                    'days' => 'required',
                    'from' => 'required',
                    'to' => 'required',
                    'location' => 'required',
                    'lat_location' => 'required|numeric',
                    'long_location' => 'required|numeric',
                ];
            
                $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
                if ($validator->passes()) {
        
                    $salon_code = sprintf("%06d", mt_rand(1, 999999));
        
                    if ($this->isInviteNumberExists($salon_code)) {
                        $salon_code = $this->generateInviteCode();
                    }
        
                    $data['salon_code'] = $salon_code;
                    $data['is_available'] = 0;
                    $data['is_open'] = 0;
            
                    
                    if(!$request->hasFile('image')) {
                         return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
                    }
            
                    $file = $request->file('image'); 
                    
                    $imageUrl = FileHelper::processImage($file, 'public/salons');
                    
                    $data['image']= $imageUrl;
                        
                    $resource = $this->salonRepository->create($data);
                    $user->salon_id =  $resource->id;
                    $user->save();
                
                    $days = $data['days'];
                    $days = json_decode($days, true);
                    
                    $from = $data['from'];
                    $from = json_decode($from, true);
            
                    $to = $data['to'];
                    $to = json_decode($to, true);
        
                    if (is_array($days)) {
                        if (isset($days[0])) {
            
                            foreach ($days as $key => $day) {

                            $fromTime = Carbon::createFromFormat('H:i:s', $from[$key]);
                            $toTime = Carbon::createFromFormat('H:i:s', $to[$key]);

                            if($toTime->gte($fromTime)  ){
                            
                                $timing = new Timing();
            
                                $timing->salon_id = $resource->id;
                                $timing->from = $from[$key];
                                $timing->to =  $to[$key];
                                $timing->day = $day;
            
                                $timing->save();
                              }
                              else{
                                return JsonResponse::respondError("your time is incorrect");
                              }
                            }
                        }
                    }
                    else{return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);}
                
                    if (!$resource) return JsonResponse::respondError(JsonResponse::MSG_CREATION_ERROR);
                    return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY), $resource);
            }
            return JsonResponse::respondError($validator->errors()->all());
        }

        $validation_rules = [
            'city_id' => "exists:cities,id",
            'location' => 'numeric',
            'lat_location' => 'numeric',
            'long_location' => 'numeric',
        ];
      
        $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
        if ($validator->passes()) {

            $salon->name = isset($data['name']) ? $data['name'] : $salon->name;
            $salon->berbers_num = isset($data['berbers_num']) ? $data['berbers_num'] : $salon->berbers_num;

            $salon->city_id = isset($data['city_id']) ? $data['city_id'] : $salon->city_id;
            $salon->type = isset($data['type']) ? $data['type'] : $salon->type;

            $salon->location = isset($data['location']) ? $data['location'] : $salon->location;
            $salon->lat_location = isset($data['lat_location']) ? $data['lat_location'] : $salon->lat_location;
            $salon->long_location = isset($data['long_location']) ? $data['long_location'] : $salon->long_location;        
            
            if($request->hasFile('image')) {
                $file = $request->file('image'); 
            
                $imageUrl = FileHelper::processImage($file, 'public/salons');
                
                $salon['image']= $imageUrl;
                $salon->image =  $imageUrl;

            }
        
            $days = $data['days'];
            $days = json_decode($days, true);
            
            $from = $data['from'];
            $from = json_decode($from, true);

            $to = $data['to'];
            $to = json_decode($to, true);

            if (is_array($days)) {
                if(count($days) == count($to) && count($days) == count($from) ){ 
                    if (isset($days[0])) {
                        
                        foreach ($days as $key => $day) {

                            $fromTime = Carbon::createFromFormat('H:i:s', $from[$key]);
                            $toTime = Carbon::createFromFormat('H:i:s', $to[$key]);

                            if($toTime->gte($fromTime)  ){

                            Timing::where('salon_id' , $salon->id)->delete();

                            $timing = new Timing();

                            $timing->salon_id = $salon->id;
                            $timing->from = $from[$key];
                            $timing->to =  $to[$key];
                            $timing->day = $day;

                            $timing->save();
                        }
                    }
                    }
                }
                else{
                    return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
                    }
            
            }
            
            $salon->save();
            return JsonResponse::respondSuccess(JsonResponse::MSG_UPDATED_SUCCESSFULLY);
        }
        return JsonResponse::respondError($validator->errors()->all());
    }


    public function CompleteSalonInfo(Request $request)
    {
    
        $user = Auth::guard('client')->user();
           
        $salon_id = $user->salon->id;
         
        $data = $this->requestData;

        $validation_rules = [
            'description' => "required",
            'bio' => "required",
        ];
        $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
        if ($validator->passes()) {

            $resource = Salon::find($salon_id);
             
             if($resource){
                
              if($resource->status == Constants::STATUS_ACCEPTED ){

                $berbers_num = $resource->berbers_num;
               
                if (count($resource->barbers) == 0 ){
                    for($i = 0 ; $i < $berbers_num ; $i++){
                
                        $barber = [];
    
                        $barber['salon_id'] = $salon_id ;
                    
                        $barber_code = sprintf("%06d", mt_rand(1, 999999));

                        if ($this->isBarberCodeExists($salon_code)) {
                            $barber_code = $this->generateInviteCode();
                        }

                        $barber['barber_code']= $barber_code;

                        //$barber['salon_code']= $resource->salon_code;
    
                        $password = sprintf("%06d", mt_rand(1, 999999));
                                
                        $barber['password']= $password;
                        $barber['city_id'] = 2;
                        
                        $this->barberRepository->create($barber);
                        
                    }
                }
            
                if (isset($data['facebook_barberRepositorylink'])  ){
                    
                    $resource->facebook_link = $data['facebook_link'];
                
                }
                if(isset($data['whatsapp_number'] )){

                    $resource->whatsapp_number = $data['whatsapp_number'];
                }
                $resource->save();
                
                $updated = $this->salonRepository->update($data, $resource->id);
                    if (!$updated) return JsonResponse::respondError(trans(JsonResponse::MSG_UPDATE_ERROR));
                    return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
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

    
    /**
 * @OA\GET(
 * path="/api/client/getAcceptedSalons",
 * summary="Get",
 * description="GET all accepted salons ",
 * tags={"Client/Salons"},
 * 
*   @OA\Response(
*     response=200,
*     description="Success",
*  ),
 * )
 */


    //get all accepted salons
    public function getAcceptedSalons(){

     
        $request_data = $this->requestData;

        $data = $this->salonRepository->allAsQuery();

        $data = $data->Where("status", '=', "Accepted");

        $data = $data->get();
  

        if($data){

            return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
        }
        else{
            return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
        }
        
    }

       /**
 * @OA\GET(
 * path="/api/client/getSalonsDeatails",
 * summary="Get",
 * description="GET all salons details ",
 * tags={"Client/Salons"},
 * 
*   @OA\Response(
*     response=200,
*     description="Success",
*  ),
 * )
 */

    //get my salon
    public function getMySalon(){

        $salon_id = Auth::guard('client')->user()->salon->id;
        
        $request_data = $this->requestData;

        $data = Salon::where('id' ,$salon_id)->where('status' , Constants::STATUS_ACCEPTED)->first();
       
        if($data){

            $barbers = $data->barbers;
            return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
        }

           return JsonResponse::respondError("Your salon is pending");  
    }

    public function show($id)
    {
        $category = Category::find($id);

        if($category){
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_SUCCESS), $category);
        }
        else{
            if (is_numeric($id)){
                return JsonResponse::respondError(JsonResponse::MSG_NOT_FOUND);
            }

            return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
        }  
    }

    



    // public function categories(){
    //     $data = $this->categoryRepository->allAsQuery()->get();
    //     return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS,$data);
    // }

   
    public function find(){

        
        $request_data = $this->requestData;

        $data = $this->salonRepository->allAsQuery();

        $data->Where("status", Constants::STATUS_ACCEPTED);
        
        if (isset($this->requestData['type'])){
            $data->Where("type", "=" , $request_data['type']);
        }

        $data = $data ->get();

        return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS,$data);

    }


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

    function isBarberCodeExists($number)
    {
        $barber_code = Barber::where('barber_code', '=', $number)->first();

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
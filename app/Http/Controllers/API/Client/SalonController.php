<?php

namespace App\Http\Controllers\API\Client;

use App\Helpers\Constants;
use App\Helpers\JsonResponse;
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


         /**
 * @OA\Post(
 * path="/api/client/createSalon",
 * summary="Store",
 * description="Create Salon with its barbers",
 * tags={"Client/Salons"},
 * @OA\RequestBody(
 *    required=true,
 *    description="Pass salon data",
 *    @OA\JsonContent(
 *       required={"name","city_id" , "type" , "barber_num"},
 *       @OA\Property(property="title", type="string", format="title", example="title1"),
 *       @OA\Property(property="city_id", type="integer", format="city_id", example="1"),
 *       @OA\Property(property="type", type="string", format="type", example="male"),
 *       @OA\Property(property="barber_num", type="integer", format="barber_num", example="2"),
 *    ),
 * ),
*   @OA\Response(
 *     response=200,
 *     description="Success",
 *  ),
 * )
 */

    //Create salon by user and create its barberes //TODO what required
    public function store(Request $request)
    {
    
        $user = Auth::guard('client')->user();
    
        $data = $this->requestData;
        $validation_rules = [
            'name' => "required",
            'city_id' => "required",
            'type' => "required",
            'days' => 'required',
            'from' => 'required',
            'to' => 'required',
            'location' => 'required',
            'lat_location' => 'required',
            'long_location' => 'required',
        ];
      
        // $sal  = new Salon();
        // $sal->name = "asda";
        // $sal->user_id = 1;
        // $sal->city_id = 2;
        // $sal->type = "";
        // $sal->location = "";
        // $sal->lat_location = "";
        // $sal->long_location = "";

        // $sal->salon_code = "sa";
        // $sal->is_available = 0;   
        // $sal->is_open = 0;
        
        // $sal->save();
        $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
        if ($validator->passes()) {

         
           
            $salon_code = sprintf("%06d", mt_rand(1, 999999));

            if ($this->isInviteNumberExists($salon_code)) {
                $salon_code = $this->generateInviteCode();
            }

           $data['salon_code'] = $salon_code;
           $data['user_id'] = $user->id;
           $data['is_available'] = 0;
           $data['is_open'] = 0;
           
          
        if(!$request->hasFile('image')) {
            return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
        }

           
        $file = $request->file('image'); 
               
        
        $imageUrl = FileHelper::processImage($file, 'public/salons');
        
        $data['image']= $imageUrl;
             
          

        $resource = $this->salonRepository->create($data);

        $days = $data['days'];
        $days = json_decode($days, true);
        
        $from = $data['from'];
        $from = json_decode($from, true);

        $to = $data['to'];
        $to = json_decode($to, true);


        if (is_array($days)) {
            if (isset($days[0])) {

                foreach ($days as $key => $day) {
                    $timing = new Timing();

                    $timing->salon_id = $resource->id;
                    $timing->from = $from[$key];
                    $timing->to =  $to[$key];
                    $timing->day = $day;

                    $timing->save();
                }
            }
        }
          else{return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);}
          
            if (!$resource) return JsonResponse::respondError(JsonResponse::MSG_CREATION_ERROR);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY), $resource);
        }
        return JsonResponse::respondError($validator->errors()->all());
    }

    public function CompleteSalonInfo(Request $request)
    {
    
        $user = Auth::guard('client')->user();
           
        $salon_id = $user->salon->first()->id;
        
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
               
               for($i = 0 ; $i<$berbers_num ;$i++){
            

                $barber = [];
                 
                $barber['salon_id'] = $salon_id ;
            
                $barber['salon_code']= $resource->salon_code;

                $password = sprintf("%06d", mt_rand(1, 999999));
                           
                $barber['password']= $password;
                 
                $barber = $this->barberRepository->create($barber);
                
              }
              if (isset($data['facebook_link'])  ){
                
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

    //get salons with it's details by id.
    public function getSalonsDetails($id){

        $request_data = $this->requestData;

        $data = Salon::find($id);
        
        if($data){

            $barbers = $data->barbers;
            return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
        }

        else{
            if (is_numeric($id)){
                return JsonResponse::respondError(JsonResponse::MSG_NOT_FOUND);
            }

            return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
        } 
        
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

  
}
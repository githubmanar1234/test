<?php

namespace App\Http\Controllers\API\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Constants;
use App\Helpers\FileHelper;
use App\Helpers\JsonResponse;
use App\Helpers\Mapper;
use App\Models\Category;
use App\Models\Country;
use App\Models\Salon;
use App\Helpers\ValidatorHelper;
use App\Http\Repositories\IRepositories\IServiceRepository;
use App\Http\Repositories\IRepositories\ISalonRepository;
use App\Http\Repositories\IRepositories\ICategoryRepository;
use App\Http\Repositories\IRepositories\IUserRepository;
use App\Http\Repositories\IRepositories\ICityRepository;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SalonController extends Controller
{
    private $userRepository;
    private $cityRepository;
    private $serviceRepository;
    private $categoryRepository;
    private $salonRepository;
    private $requestData;
    private $authUser;

    public function __construct(
        IServiceRepository $serviceRepository,
        ICityRepository $cityRepository,
        ICategoryRepository $categoryRepository,
        ISalonRepository $salonRepository,
        IUserRepository $userRepository
    )
    {
        $this->salonRepository = $salonRepository;
        $this->cityRepository = $cityRepository;
        $this->userRepository = $userRepository;
        $this->serviceRepository = $serviceRepository;
        $this->categoryRepository = $categoryRepository;
        $this->requestData = Mapper::toUnderScore(\Request()->all());
        $this->authUser = Auth::guard('admin')->user();
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
 * @OA\GET(
 * path="/api/admin/getPendingSalons",
 * summary="Get",
 * description="GET all pending salons ",
 * tags={"Dashboard/Salons"},
 * 
*   @OA\Response(
*     response=200,
*     description="Success",
*  ),
 * )
 */

    public function getPendingSalons()
    {
        $request_data = $this->requestData;

        $data = $this->salonRepository->allAsQuery();

        $data = $data->Where("status", '=', "Pending");

        $data = $data->paginate(15);
  

        if($data){

            return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
        }
        else{
            return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
        }
        
          
    }


    
    // public function show($id)
    // {
    //     $data =  $this->salonRepository->find($id);
        
    //     if($data){

    //         $data = $data->salonReports;
    //         return JsonResponse::respondSuccess(trans(JsonResponse::MSG_SUCCESS), $data);
    //     }
    //     return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
    // }


    
    //To test just 
    // public function store(Request $request)
    // {
    //     $data = $this->requestData;
    //     $validation_rules = [
    //         'name' => "required",
    //         'user_id' => "required",
    //         'city_id' => "required",
    //         'salon_code' => "required",
           
    //     ];
    //     $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
    //     if ($validator->passes()) {

        
    //         $resource = $this->salonRepository->create($data);
        
    //         if (!$resource) return JsonResponse::respondError(JsonResponse::MSG_CREATION_ERROR);
    //         return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY), $resource);
    //     }
    //     return JsonResponse::respondError($validator->errors()->all());
    // }

    /**
 * @OA\GET(
 * path="/api/admin/getAcceptedAndRejectedSalons",
 * summary="Get",
 * description="GET all accepted and rejected salons ",
 * tags={"Dashboard/Salons"},
 * 
*   @OA\Response(
*     response=200,
*     description="Success",
*  ),
 * )
 */
    public function getAcceptedAndRejectedSalons()
    {
        $request_data = $this->requestData;

        $data = $this->salonRepository->allAsQuery();

        $data = $data->Where("status", '=', "Rejected")->orWhere("status", '=', "Accepted");

        $data = $data->paginate(15);

        if($data){
            
            return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
        }
        else{
            return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
        }
        
    }

    public function getAcceptedAndDisabledSalons()
    {
        $request_data = $this->requestData;

        $data = $this->salonRepository->allAsQuery();

        $data = $data->Where("status", Constants::STATUS_DISABLE)->orWhere("status", Constants::STATUS_ACCEPTED);

        $data = $data->paginate(15);
        // $data = $data->get();

        if($data){
            
            return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
        }
        else{
            return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
        }
        
          
    }

     /**
 * @OA\Post(
 * path="/api/admin/setAcceptedSalon",
 * summary="Store",
 * description="Set accepted Salon",
 * tags={"Dashboard/Salons"},
 * @OA\RequestBody(
 *    required=true,
 *    description="Pass id for salon ",
 *    @OA\JsonContent(
 *       required={"salon_id"},
 *       @OA\Property(property="salon_id", type="integer", format="salon_id", example="1"),
 *    ),
 * ),
*   @OA\Response(
 *     response=200,
 *     description="Success",
 *  ),
 * )
 */

    public function setAcceptedSalon(Request $request)
    {
        $request_data = $this->requestData;
        $validation_rules = [
            'salon_id' => "required",
        ];

        $validator = Validator::make($request_data, $validation_rules, ValidatorHelper::messages());
        if ($validator->passes()) {

            $data = $this->salonRepository->allAsQuery();

            $data = $data->find($request_data['salon_id']);

            if($data){

                if($data->status == Constants::STATUS_PENDING || $data->status == Constants::STATUS_DISABLE){
    
                    $data->status = "Accepted" ;
                    $data->is_available = 1 ;
                    $data->save();
                    return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
                }  
                else{
                    return JsonResponse::respondError("Salon not pending or disabled");
                    } 
            }
             else{
                if (is_numeric($request_data['salon_id'])){
                    return JsonResponse::respondError(JsonResponse::MSG_NOT_FOUND);
                }
    
                return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
              }
        }
        return JsonResponse::respondError($validator->errors()->all());
    }

    
 /**
 * @OA\Post(
 * path="/api/admin/setRejectedSalon",
 * summary="Store",
 * description="Set rejected Salon",
 * tags={"Dashboard/Salons"},
 * @OA\RequestBody(
 *    required=true,
 *    description="Pass id for salon ",
 *    @OA\JsonContent(
 *       required={"salon_id"},
 *       @OA\Property(property="salon_id", type="integer", format="salon_id", example="1"),
 *    ),
 * ),
*   @OA\Response(
 *     response=200,
 *     description="Success",
 *  ),
 * )
 */

    public function setRejectedSalon(Request $request)
    {
        $request_data = $this->requestData;
        $validation_rules = [
            'salon_id' => "required",
            'reason' => "required",
        ];

        $validator = Validator::make($request_data, $validation_rules, ValidatorHelper::messages());
        if ($validator->passes()) {

            $data = $this->salonRepository->allAsQuery();

            $data = $data->find($request_data['salon_id']);

            if($data){

                if($data->status == Constants::STATUS_PENDING){
    
                    $data->status = "Rejected" ;                 
                    $data->reason = $request_data['reason'] ;
                    $data->is_available = 0 ;
                    $data->save();
                    return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
                }  
                else{
                    return JsonResponse::respondError("Salon not Pending");
                    } 
            }
             else{
                if (is_numeric($request_data['salon_id'])){
                    return JsonResponse::respondError(JsonResponse::MSG_NOT_FOUND);
                }
    
                return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
              }
        }
        return JsonResponse::respondError($validator->errors()->all());
    }

    
 /**
 * @OA\Post(
 * path="/api/admin/setDisabledSalon",
 * summary="Store",
 * description="Set disabled Salon",
 * tags={"Dashboard/Salons"},
 * @OA\RequestBody(
 *    required=true,
 *    description="Pass id for salon ",
 *    @OA\JsonContent(
 *       required={"salon_id"},
 *       @OA\Property(property="salon_id", type="integer", format="salon_id", example="1"),
 *    ),
 * ),
*   @OA\Response(
 *     response=200,
 *     description="Success",
 *  ),
 * )
 */
    public function setDisabledSalon(Request $request)
    {
        $request_data = $this->requestData;
        $validation_rules = [
            'salon_id' => "required",
            'reason' => "required",
        ];

        $validator = Validator::make($request_data, $validation_rules, ValidatorHelper::messages());
        if ($validator->passes()) {

            $data = $this->salonRepository->allAsQuery();

            $data = $data->find($request_data['salon_id']);

            if($data){

                if($data->status == Constants::STATUS_ACCEPTED){
    
                    $data->status = "Disable" ;                 
                    $data->reason = $request_data['reason'] ;
                    $data->is_available = 0 ;
                    $data->save();
                    return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
                }  
                else{
                    return JsonResponse::respondError("Salon not accepted");
                    } 
            }
             else{
                if (is_numeric($request_data['salon_id'])){
                    return JsonResponse::respondError(JsonResponse::MSG_NOT_FOUND);
                }
    
                return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
              
              }
        }
        return JsonResponse::respondError($validator->errors()->all());
    }


    public function destroy($id)
    {
        $resource = Salon::find($id);
      
        if($resource){

            $this->salonRepository->delete($resource);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        }

         else{
             
            if (is_numeric($id)){
                return JsonResponse::respondError(JsonResponse::MSG_NOT_FOUND);
            }

            return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
        }  
    
    }

  
}

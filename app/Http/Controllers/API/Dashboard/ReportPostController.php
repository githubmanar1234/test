<?php

namespace App\Http\Controllers\API\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Constants;
use App\Helpers\FileHelper;
use App\Helpers\JsonResponse;
use App\Helpers\Mapper;
use App\Models\Category;
use App\Models\Salon;
use App\Helpers\ValidatorHelper;
use App\Http\Repositories\IRepositories\IServiceRepository;
use App\Http\Repositories\IRepositories\IPostRepository;
use App\Http\Repositories\IRepositories\ICategoryRepository;
use App\Http\Repositories\IRepositories\IUserRepository;
use App\Http\Repositories\IRepositories\IPostReportRepository;
use App\Http\Repositories\IRepositories\ISalonReportRepository;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class ReportPostController extends Controller
{
    private $userRepository;
    private $postRepository;
    private $postReportRepository;
    private $requestData;
    private $authUser;

    public function __construct(
        IPostRepository $postRepository,
        IUserRepository $userRepository,
        IPostReportRepository $postReportRepository
    )
    {
        $this->postReportRepository = $postReportRepository;
        $this->postRepository = $postRepository;
        $this->userRepository = $userRepository;
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
 * path="/api/admin/getReportedPosts",
 * summary="Get",
 * description="GET all reported posts ",
 * tags={"Dashboard/Reports"},
 * 
*   @OA\Response(
*     response=200,
*     description="Success",
*  ),
 * )
 */

    public function getReportedPosts()
    {
        $request_data = $this->requestData;

        $data = $this->postRepository->reportedPosts();
  
        if($data){
            return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
        }
        else{
            return JsonResponse::respondError("There are not reported posts");
        }
       
          
    }

      /**
 * @OA\GET(
 * path="/api/admin/getReportByPost/{id}",
 * summary="Show",
 * description="GET report by post's id",
 * tags={"Dashboard/Reports"},
 *      @OA\Parameter(
 *         name="id",
 *         in="query",
 *         required=true,
 *      ),
 *   @OA\Response(
 *     response=200,
 *     description="Success",
 *  ),
 * )
 */

    public function show($id)
    {
        
        // $data =  $this->postRepository->find($id);
        $data = $this->postRepository->allAsQuery();
        $data = $data->find($id);
        
        if($data){

            $data = $data->postReports;
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_SUCCESS), $data);
        }
        else{

            if (is_numeric($id)){
                return JsonResponse::respondError(JsonResponse::MSG_NOT_FOUND);
            }

            return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
        }
    }

     //To test just 
     public function store(Request $request)
     {
         $data = $this->requestData;
         $validation_rules = [
             'reason' => "required",
             'salon_id' => "required",
             'user_id' => "required",   
         ];
         $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
         if ($validator->passes()) {
 
        
             $resource = $this->salonReportRepository->create($data);
         
             if (!$resource) return JsonResponse::respondError(JsonResponse::MSG_CREATION_ERROR);
             return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY), $resource);
         }
         return JsonResponse::respondError($validator->errors()->all());
     }

  
}

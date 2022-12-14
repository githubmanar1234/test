<?php

namespace App\Http\Controllers\API\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Constants;
use App\Helpers\FileHelper;
use App\Helpers\JsonResponse;
use App\Helpers\Mapper;
use App\Models\Category;
use App\Helpers\ValidatorHelper;
use App\Http\Repositories\IRepositories\IServiceRepository;
use App\Http\Repositories\IRepositories\ICategoryRepository;
use App\Http\Repositories\IRepositories\IUserRepository;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
 
 
class ServiceController extends Controller
{
    private $userRepository;
    private $serviceRepository;
    private $categoryRepository;
    private $requestData;
    private $authUser;

    public function __construct(
        IServiceRepository $serviceRepository,
        ICategoryRepository $categoryRepository,
        IUserRepository $userRepository
    )
    {
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
 * path="/api/admin/getAllServices",
 * summary="Index",
 * description="GET all services",
 * tags={"Dashboard/Services"},
 * 
*   @OA\Response(
*     response=200,
*     description="Success",
*  ),
 * )
 */

    public function index()
    {
        $data = $this->serviceRepository->all();
      
        if (!$data) return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
        return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
    }
    
      /**
 * @OA\GET(
 * path="/api/admin/getService/{id}",
 * summary="Show",
 * description="GET Service id",
 * tags={"Dashboard/Services"},
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

    // public function show($id)
    // {
    //     $service = Service::find($id);
    //     if($service){
    //         return JsonResponse::respondSuccess(trans(JsonResponse::MSG_SUCCESS), $service);
    //     }
    //     return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
    // }

    public function show($id)
    {
        $service = Service::find($id);

        if($service){
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_SUCCESS), $service);
        }
        else{
            if (is_numeric($id)){
                return JsonResponse::respondError(JsonResponse::MSG_NOT_FOUND);
            }

            return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
        }  
    }


         /**
 * @OA\GET(
 * path="/api/admin/services/find",
 * summary="Find",
 * description="GET Service by title , description or both",
 * tags={"Dashboard/Services"},
 *      @OA\Parameter(
 *         name="title",
 *         in="query",
 *         required=true,
 *      ),
 *   @OA\Response(
 *     response=200,
 *     description="Success",
 *  ),
 * )
 */

    public function find()
    {
        $request_data = $this->requestData;

        $data = $this->serviceRepository->allAsQuery();
        if (isset($this->requestData['title'])) foreach (Constants::LANGUAGES as $LANGUAGE) {
            $data = $data->orWhere("title->" . $LANGUAGE, 'like', "%".$request_data['title']."%");
        }
        if (isset($this->requestData['description'])) foreach (Constants::LANGUAGES as $LANGUAGE) {
            $data = $data->orWhere("description->" . $LANGUAGE, 'like', "%".$request_data['description']."%");
        }

        $data = $data->get();
        return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);

    }


     /**
 * @OA\Post(
 * path="/api/admin/addService",
 * summary="Store",
 * description="Add service",
 * tags={"Dashboard/Services"},
 * @OA\RequestBody(
 *    required=true,
 *    description="Pass service data",
 *    @OA\JsonContent(
 *       required={"title","category_id"},
 *       @OA\Property(property="title", type="string", format="title", example="title1"),
 *       @OA\Property(property="category_id", type="integer", format="category_id", example="1"),
 *    ),
 * ),
*   @OA\Response(
 *     response=200,
 *     description="Success",
 *  ),
 * )
 */

    public function store(Request $request)
    {
        $data = $this->requestData;
        $validation_rules = [
            'title' => "required|array|languages",
            'title.*' => "required",
            'description' => "required|array|languages",
            'description.*' => "required",
            'category_id' => "required|exists:categories,id",
        ];
        $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
        if ($validator->passes()) {

            if($request->hasFile('image')) {
                $file = $request->file('image'); 
                   
                    $imageUrl = FileHelper::processImage($file, 'public/services');
                    $data['image']= $imageUrl;
                   
            }
            
            $resource = $this->serviceRepository->create($data);
            if (!$resource) return JsonResponse::respondError(JsonResponse::MSG_CREATION_ERROR);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY), $resource);
        }
        return JsonResponse::respondError($validator->errors()->all());
    }

     /**
 * @OA\Put(
 * path="/api/admin/editService/{id}",
 * summary="Edit",
 * description="Update service",
 * tags={"Dashboard/Services"},
 * @OA\RequestBody(
 *    required=true,
 *    description="Pass service data",
 *    @OA\JsonContent(
 *       required={"title","description","category_id"},
 *       @OA\Property(property="title", type="string", format="title", example="title1"),
 *       @OA\Property(property="description", type="string", format="description", example="title1title1"),
 *       @OA\Property(property="category_id", type="integer", format="category_id", example="1"),
 *    ),
 * ),
*   @OA\Response(
 *     response=200,
 *     description="Success",
 *  ),
 * )
 */

    public function updateService(Request $request, $id) 
    {
        $data = $this->requestData;
        $validation_rules = [
            'title' => "required|array|languages",
            'title.*' => "required",
            'description' => "required|array|languages",
            'description.*' => "required",
            'category_id' => "required|exists:categories,id",
        ];

        $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
        if ($validator->passes()) {
          
            $resource = Service::find($id);

            if($resource){
                if (isset($data['order'])) unset($data['order']);
                $updated = $this->serviceRepository->update($data, $resource->id);
                if (!$updated) return JsonResponse::respondError(trans(JsonResponse::MSG_UPDATE_ERROR));
                return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
            }     
            else{
                if (is_numeric($id)){
                    return JsonResponse::respondError(JsonResponse::MSG_NOT_FOUND);
                }
    
                return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
            }    
        }
        return JsonResponse::respondError($validator->errors()->all());
    }

    
      /**
 * @OA\Delete(
 * path="/api/admin/deleteService/{id}",
 * summary="Delete",
 * description="Delete service",
 * tags={"Dashboard/Services"},
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

    public function destroy($id)
    {
        $resource = Service::find($id);

        if($resource){

            $this->serviceRepository->delete($resource);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        }

         else{
             
            if (is_numeric($id)){
                return JsonResponse::respondError(JsonResponse::MSG_NOT_FOUND);
            }

            return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
        }  
    
    }

    /**
 * @OA\GET(
 * path="/api/admin/serviceOfCategory/find",
 * summary="Show",
 * description="GET report by salon's id",
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

    public function findByCategoryId()
    {
        $request_data = $this->requestData;

        $data = $this->serviceRepository->allAsQuery();

        if (isset($this->requestData['category_id'])) {

            $data = $data->where("category_id" , '=' , $request_data['category_id']);
            
            if($data){

                $data = $data->get();
                return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
            }
      
           
        }
    }
       

}

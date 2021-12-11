<?php

namespace App\Http\Controllers\API\Dashboard;

use App\Helpers\Constants;
use App\Helpers\FileHelper;
use App\Helpers\JsonResponse;
use App\Helpers\Mapper;
use App\Helpers\ValidatorHelper;
use App\Http\Controllers\Controller;
use App\Http\Repositories\IRepositories\ICategoryRepository;
use App\Http\Repositories\IRepositories\IUserRepository;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


/**
 * @OA\GET(
 * path="/api/admin/getAllServiceCategories",
 * summary="Index",
 * description="GET all categories",
 * tags={"Dashboard/Categories"},
 * 
*   @OA\Response(
*     response=200,
*     description="Success",
*  ),
 * )
 */

 /**
 * @OA\GET(
 * path="/api/admin/getServiceCategory/{id}",
 * summary="Show",
 * description="GET serviceCategory by id",
 * tags={"Dashboard/Categories"},
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

 /**
 * @OA\Post(
 * path="/api/admin/addServiceCategory",
 * summary="Store",
 * description="Add ServiceCategory",
 * tags={"Dashboard/Categories"},
 * @OA\RequestBody(
 *    required=true,
 *    description="Pass category data",
 *    @OA\JsonContent(
 *       required={"title","order"},
 *       @OA\Property(property="title", type="string", format="title", example="title1"),
 *       @OA\Property(property="order", type="integer", format="order", example="1"),
 *    ),
 * ),
*   @OA\Response(
 *     response=200,
 *     description="Success",
 *  ),
 * )
 */

 /**
 * @OA\Put(
 * path="/api/admin/editServiceCategory/{id}",
 * summary="Edit",
 * description="Update ServiceCategory",
 * tags={"Dashboard/Categories"},
 * @OA\RequestBody(
 *    required=true,
 *    description="Pass Category data",
 *    @OA\JsonContent(
 *       required={"title","description","category_id"},
 *       @OA\Property(property="title", type="string", format="title", example="title1"),
 *       @OA\Property(property="description", type="string", format="description", example="title1title1"),
 *    ),
 * ),
*   @OA\Response(
 *     response=200,
 *     description="Success",
 *  ),
 * )
 */

  /**
 * @OA\Delete(
 * path="/api/admin/deleteServiceCategory/{id}",
 * summary="Delete",
 * description="Delete serviceCategory",
 * tags={"Dashboard/Categories"},
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

class CategoryController extends Controller
{
    private $userRepository;
    private $categoryRepository;
    private $requestData;
    private $authUser;

    public function __construct(
        ICategoryRepository $categoryRepository,
        IUserRepository $userRepository
    )
    {
        $this->userRepository = $userRepository;
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

    public function index()
    {
        $data = $this->categoryRepository->allAsQuery()->orderBy('order')->get();
        return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
    }
    
    /**
     * find category by title or description
     * @return \Illuminate\Http\JsonResponse
     */
    public function find()
    {
        $request_data = $this->requestData;

        $data = $this->categoryRepository->allAsQuery();
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
     * * Store a newly created resource
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $this->requestData;
        $validation_rules = [
            'title' => "required|array|languages",
            'title.*' => "required",
            'description' => "required|array|languages",
            'description.*' => "required",
        ];
        $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
        if ($validator->passes()) {
            $data['order'] = 1;
            if( $this->categoryRepository->allAsQuery()->orderBy('order', 'DESC')->first()){
                $data['order'] = $this->categoryRepository->allAsQuery()->orderBy('order', 'DESC')->first()->order + 1;
            }

            $resource = $this->categoryRepository->create($data);
            if (!$resource) return JsonResponse::respondError(JsonResponse::MSG_CREATION_ERROR);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY), $resource);
        }
        return JsonResponse::respondError($validator->errors()->all());
    }

    /**
     * @param Category $category
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @author Samh Dev
     */
    public function updateCategory(Request $request, $id) 
    {
        $data = $this->requestData;
        $validation_rules = [
            'title' => "required|array|languages",
            'title.*' => "required",
            'description' => "required|array|languages",
            'description.*' => "required",
        ];

        $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
        if ($validator->passes()) {
          
            $resource = Category::find($id);

             if($resource){
                if (isset($data['order'])) unset($data['order']);

                $updated = $this->categoryRepository->update($data, $resource->id);
    
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
     * @param Int $id
     * @return \Illuminate\Http\JsonResponse
     * @author Samh Dev
     */

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

    /**
     * @param  $id
     * @return \Illuminate\Http\JsonResponse
     */
   
    public function destroy($id)
    {
        $resource = Category::find($id);

        if($resource){

            $this->categoryRepository->delete($resource);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        }

         else{
             
            if (is_numeric($id)){
                return JsonResponse::respondError(JsonResponse::MSG_NOT_FOUND);
            }

            return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
        }  
    
    }

    public function updateCategoriesOrder()
    {
        $data = $this->requestData;

        $cats = Category::all();

        $count = $cats->count();

        $validation_rules = [
            'orders' => "required|array|min:" . $count . "|max:" . $count,
            'orders.*' => "required|numeric|distinct|max:" . $count
        ];
        $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());

        if ($validator->passes()) {
            
            foreach ($data['orders'] as $key => $value) {
                if (!$cats->contains('id', $key))
                    return JsonResponse::respondError(["invalid category id " . $key]);
            }
            foreach ($cats as $cat) {
                $this->categoryRepository->update(['order' => $data['orders'][$cat->id]], $cat->id);
            }
            return JsonResponse::respondSuccess(JsonResponse::MSG_UPDATED_SUCCESSFULLY);
        }
        return JsonResponse::respondError($validator->errors()->all());
    }

    public function getCategoriesOrders()
    {

        $cats = $this->categoryRepository->allAsQuery()->orderBy('order')->select('id', 'order')->get();
        $keyed = $cats->mapWithKeys(function ($item) {
            return [$item['id'] => $item['order']];
        });
        return JsonResponse::respondSuccess(JsonResponse::MSG_UPDATED_SUCCESSFULLY, $keyed);

    }

    /**
     * @param $array
     * @return bool
     * check if array has duplicated keys
     */
    function has_dupes($array)
    {
        $dupe_array = array(count($array));
        foreach ($array as $item) {
            $dupe_array[$item] = 0;
        }
        foreach ($array as $val) {
            if (++$dupe_array[$val] > 1) {
                return true;
            }
        }
        return false;
    }
}
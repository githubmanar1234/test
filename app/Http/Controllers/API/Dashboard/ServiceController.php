<?php

namespace App\Http\Controllers\API\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Constants;
use App\Helpers\FileHelper;
use App\Helpers\JsonResponse;
use App\Helpers\Mapper;
use App\Helpers\ValidatorHelper;
use App\Http\Repositories\IRepositories\IServiceRepository;
use App\Http\Repositories\IRepositories\IUserRepository;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


/**
 * @OA\GET(
 * path="/api/admin/getAllService",
 * summary="Get all",
 * description="GET all services",
 * tags={"admin"},
 * 
*   @OA\Response(
*     response=200,
*     description="Success",
*  ),
 * )
 */


class ServiceController extends Controller
{
    private $userRepository;
    private $serviceRepository;
    private $requestData;
    private $authUser;

    public function __construct(
        IServiceRepository $serviceRepository,
        IUserRepository $userRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->serviceRepository = $serviceRepository;
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
        $data = $this->serviceRepository->all();
      
        if (!$data) return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
        return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
    }
    
    public function show($id)
    {
        $service = Service::find($id);
        if($service){
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_SUCCESS), $service);
        }
        return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
    }


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


    public function store(Request $request)
    {
        $data = $this->requestData;
        $validation_rules = [
            'title' => "required|array|languages",
            'title.*' => "required",
        ];
        $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
        if ($validator->passes()) {

            $resource = $this->serviceRepository->create($data);
            if (!$resource) return JsonResponse::respondError(JsonResponse::MSG_CREATION_ERROR);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY), $resource);
        }
        return JsonResponse::respondError($validator->errors()->all());
    }

    public function updateService(Request $request, $id) 
    {
        $data = $this->requestData;
        $validation_rules = [
            'title' => "required|array|languages",
            'title.*' => "required",
            'description' => "required|array|languages",
            'description.*' => "required",
            'category_id' => "required",
        ];

        $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
        if ($validator->passes()) {
          
            $resource = Service::find($id);

            if (isset($data['order'])) unset($data['order']);
            $updated = $this->serviceRepository->update($data, $resource->id);
            if (!$updated) return JsonResponse::respondError(trans(JsonResponse::MSG_UPDATE_ERROR));
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
        }
        return JsonResponse::respondError($validator->errors()->all());
    }

    
    public function destroy($id)
    {
        $resource = Service::find($id);
        if($resource ){
            $this->serviceRepository->delete($resource);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        }
        return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);    
    }


}

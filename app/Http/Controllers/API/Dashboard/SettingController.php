<?php

namespace App\Http\Controllers\API\Dashboard;

use App\Helpers\Constants;
use App\Helpers\JsonResponse;
use App\Helpers\Mapper;
use App\Helpers\ValidatorHelper;
use App\Http\Controllers\Controller;
use App\Http\Repositories\IRepositories\ICardRepository;
use App\Http\Repositories\IRepositories\ISettingRepository;
use App\Models\Card;
use App\Models\CardRequest;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    private $settingRepository;
    private $requestData;

    public function __construct(
        ISettingRepository $settingRepository)
    {
        $this->settingRepository = $settingRepository;
        $this->requestData = Mapper::toUnderScore(\Request()->all());
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @author samh
     * @desc get all social media tasks
     */
    public function index()
    {
        $data = $this->settingRepository->all();
        if (!$data) return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
        return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
    }

    /**
     * @param Setting $setting
     * @return \Illuminate\Http\JsonResponse
     * @author Samh Dev
     */
    public function show(Setting $setting)
    {
        return JsonResponse::respondSuccess(trans(JsonResponse::MSG_SUCCESS), ["data" => $setting]);
    }

    /**
     * Store a newly created resource
     * @return \Illuminate\Http\JsonResponse
     * @author Samh Dev
     */
    public function store()
    {
        $data = $this->requestData;
        $validation_rules = self::customizeRules($data);
        $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
        if ($validator->passes()) {
            $resource = $this->settingRepository->create($data);
            if (!$resource) return JsonResponse::respondError(JsonResponse::MSG_CREATION_ERROR);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY), $resource);
        }
        return JsonResponse::respondError($validator->errors()->all());
    }

    /**
     * @param Setting $setting
     * @return \Illuminate\Http\JsonResponse
     * @author Samh Dev
     */
    public function updateKey()
    {
        if (!getActivePolling()){
            $data = $this->requestData;
            $validation_rules = self::customizeRules($data);
            $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
            if ($validator->passes()) {
                $resource = $this->settingRepository->findBy('key', $this->requestData['key']);
                if ($resource) {
                    $updated = $this->settingRepository->update($data, $resource->id);
                    if (!$updated) return JsonResponse::respondError(trans(JsonResponse::MSG_UPDATE_ERROR));
                    return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
                } else {
                    return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
                }
            }
            return JsonResponse::respondError($validator->errors()->all());
        }
        else
            return JsonResponse::respondError(JsonResponse::CANNOT_UPDATE_SETTING_DURING_ACTIVE_POLL);
    }

    public function updateSetting(Request $request, $id) 
    {

            $data = $this->requestData;
     
            $resource = Setting::find($id);

             if($resource){

                if (isset($data['key'])  ){
                
                    $resource->key = $data['key'];
                   
                }
                if(isset($data['value'] )){
    
                    $resource->value = $data['value'];
                }
                if(isset($data['default'] )){
    
                    $resource->default = $data['default'];
                }
                if(isset($data['description'] )){
    
                    $resource->description = $data['description'];
                }
                $resource->save();
            
                $updated = $this->settingRepository->update($data, $resource->id);
    
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
    

    /**
     * @param Setting $setting
     * @return \Illuminate\Http\JsonResponse
     * @author Samh Dev
     */
    public function getByKey()
    {

        $data = $this->requestData;
        $validation_rules = ['key' => 'required'];
        $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
        if ($validator->passes()) {
            $resource = $this->settingRepository->findBy('key', $this->requestData['key']);
            if ($resource) {
                return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY), $resource);
            } else {
                return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
            }
        }
        return JsonResponse::respondError($validator->errors()->all());
    }

    /**
     * @param Setting $setting
     * @return \Illuminate\Http\JsonResponse
     * @author Samh Dev
     */
    public function destroy(Setting $setting)
    {
        $resource = $setting;
        $this->settingRepository->delete($resource);
        return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
    }

    /**
     * customize rules
     * @param $data
     * @return array
     * @author Samh Dev
     */
    public static function customizeRules($data)
    {
        $validation_rules = Setting::create_update_rules;
        return $validation_rules;
    }
}

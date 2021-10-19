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
    
}

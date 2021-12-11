<?php

namespace App\Http\Controllers\API\Client;

use App\Helpers\Constants;
use App\Helpers\FileHelper;
use App\Helpers\JsonResponse;
use App\Helpers\Mapper;
use App\Helpers\ValidatorHelper;
use App\Http\Controllers\Controller;
use App\Http\Repositories\IRepositories\IUserRepository;
use App\Models\Image;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private $userRepository;
    private $requestData;
    private $authUser;

    public function __construct(
        IUserRepository $userRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->requestData = Mapper::toUnderScore(\Request()->all());
        $this->authUser = Auth::guard('client')->user();
    }


    /**
     * get user info
     * @return \Illuminate\Http\JsonResponse
     * @author Samh Dev
     */
    public function userInfo()
    {

        $user = $this->authUser;
        //$user->services;
        // $user->append('workExperienceTitles');
        return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $user);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     *  get user by id
     */
    public function userById($id)
    {

        $user = User::find($id);

        if($user){

            unset($user->created_at);
            unset($user->updated_at);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_SUCCESS), $user);
        }
        else{
            if (is_numeric($id)){
                return JsonResponse::respondError(JsonResponse::MSG_NOT_FOUND);
            }

            return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
        }  
        
    }


    /**
     * update user info
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @author Samh Dev
     */
    public function updateInfo(Request $request)
    {
        $authUser = Auth::guard('client')->user();

        $data = $this->requestData;
        $validation_rules = [
            'yob' => 'date',
            'country_id' => 'exists:countries,id',
            'profile_image' => 'mimes:jpg,jpeg,png,jpg|max:2048|nullable',
        ];

        $data['profile_image'] = $request->file('profileImage');
        
        $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
        if ($validator->passes()) {

            $user = \App\Models\User::find($authUser->id);

            $user->name = isset($data['name']) ? $data['name'] : $user->name;
            $user->description = isset($data['description']) ? $data['description'] : $user->description;
            $user->country_id = isset($data['country_id']) ? $data['country_id'] : $user->country_id;

            if($request->hasFile('profileImage')) {
                $file = $request->file('profileImage'); 
               
                $imageUrl = FileHelper::processImage($file, 'public/salons');
                
                $userData['profile_image']= $imageUrl;
                $user->profile_image =  $imageUrl;

            }

             $user->save();
             return JsonResponse::respondSuccess(JsonResponse::MSG_UPDATED_SUCCESSFULLY);
        } else {
            return JsonResponse::respondError($validator->errors()->all());
        }

    }

}

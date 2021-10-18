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
        $user->images;
        $user->services;
        // $user->append('workExperienceTitles');
        return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $user);
    }

    /**
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     *  get user by id
     */
    public function userById(User $user)
    {


        $user->images;
        // $user->append('workExperienceTitles');
        unset($user->created_at);
        unset($user->updated_at);
        return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $user);
    }


    /**
     * update user info
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @author Samh Dev
     */
    public function updateInfo(Request $request)
    {
        $authUser = Auth::guard('client')->user();;
        $data = $this->requestData;
        $validation_rules = [
            'name' => 'required',
            'yob' => 'required|date',
            'email' => 'email|nullable',
            'country_id' => 'required|exists:countries,id',
            'profile_image' => 'mimes:jpg,jpeg,png,jpg|max:2048|nullable',
            'location' => ['nullable', 'regex:/^[-]?((([0-8]?[0-9])(\.(\d{1,8}))?)|(90(\.0+)?)),\s?[-]?((((1[0-7][0-9])|([0-9]?[0-9]))(\.(\d{1,8}))?)|180(\.0+)?)$/'],
            'images.*' => ['nullable', 'mimes:jpeg,png,jpg|max:2048'],
            'lang' => "required|" . Rule::in(Constants::LANGUAGES)
        ];
        $data['profile_image'] = $request->file('profileImage');
        $data['images'] = $request->file('images');
        $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
        if ($validator->passes()) {
            $userData['name'] = $data['name'];
            $userData['yob'] = $data['yob'];
            $userData['lang'] = $data['lang'];
            // $userData['work_experience'] = $data['work_experience'];
            $userData['country_id'] = $data['country_id'];
            $userData['email'] = isset($data['email']) ? $data['email'] : null;
            //$userData['profile_image'] = isset($data['profile_image']) ? $data['profile_image'] : $authUser->profile_image;
            $userData['location'] = isset($data['location']) ? $data['location'] : null;
            $userData['whatsapp_number'] = isset($data['whatsapp_number']) ? $data['whatsapp_number'] : null;
            $userData['skills'] = isset($data['skills']) ? $data['skills'] : null;
            $profile_image = $data['profile_image'];
            $user = \App\Models\User::find($authUser->id);
            $user->name = $userData['name'];
            $user->yob = $userData['yob'];
            $user->lang = $userData['lang'];
            // $user->work_experience = $userData['work_experience'];
            $user->country_id = $userData['country_id'];
            $user->email = $userData['email'];
            $user->location = $userData['location'];
            $user->whatsapp_number = $userData['whatsapp_number'];
            $user->skills = $userData['skills'];
            if (isset($profile_image)) {
                $userData['profile_image'] = FileHelper::processImage($profile_image, 'public/images/users/profile');
            } else {
                if (File::exists(public_path($authUser->profile_image))) {
                    File::delete(public_path($authUser->profile_image));
                }
                $userData['profile_image'] = null;
            }
            $user->profile_image = $userData['profile_image'];
            $images = $data['images'];
            foreach ($authUser->images as $image) {

                if (File::exists(public_path($image->path))) {
                    File::delete(public_path($image->path));
                }
                $image->delete();
            }
            if ($images) {
                foreach ($images as $image) {
                    $imageUrl = FileHelper::processImage($image, 'public/images/users');
                    $imageItem = new Image;
                    $imageItem->path = $imageUrl;
                    $authUser->images()->save($imageItem);
                }
            }
            $user->save();
            // $this->userRepository->update($userData, $authUser->id);
            return JsonResponse::respondSuccess(JsonResponse::MSG_UPDATED_SUCCESSFULLY);
        } else {
            return JsonResponse::respondError($validator->errors()->all());
        }

    }


}

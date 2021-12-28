<?php

namespace App\Http\Controllers\API\Client\Auth;


use App\Exceptions\GeneralException;
use App\Helpers\Constants;
use App\Helpers\Helpers;
use App\Helpers\Mapper;
use App\Helpers\JsonResponse;
use App\Helpers\ResponseStatus;
use App\Helpers\ValidatorHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Repositories\IRepositories\IUserRepository;
use Illuminate\Validation\Rule;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Firebase\Factory;
use App\Http\Repositories\IRepositories\ISalonRepository;
use App\Http\Repositories\IRepositories\IBarberRepository;
use App\Models\Salon;
use App\Models\Barber;

class AuthController extends Controller
{
    protected $userRepository;
    protected $salonRepository;
    protected $authMethodRepository;
    protected $requestData;
    protected $factory;
    protected $messaging;

    public function __construct(
        IUserRepository $userRepository,
        ISalonRepository $salonRepository,
        IBarberRepository $barberRepository

    )
    {
        $this->userRepository = $userRepository;
        $this->barberRepository = $barberRepository;
        $this->salonRepository = $salonRepository;

        $this->requestData = Mapper::toUnderScore(\Request()->all());
        $this->factory = (new Factory)->withServiceAccount(base_path("sihhatler-olsun-firebase-adminsdk-h9gs0-f3ae80cb6e.json"));
    }
    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Kreait\Firebase\Exception\FirebaseException
     * @throws \Kreait\Firebase\Exception\MessagingException
     */
    public function login()
    {

        $data = $this->requestData;
        $validation_rules = [
            'phone' => 'required',
            'password' => 'required'
        ];
        $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
        if ($validator->passes()) {
            if (isset($data['phone']))
                $dbUser = $this->userRepository->findBy('phone', $data['phone']);

            $deviceName = isset($this->requestData['device_name']) ? $this->requestData['device_name'] : "access token";
            if (!$dbUser) {
                return JsonResponse::respondError(JsonResponse::MSG_LOGIN_FAILED, ResponseStatus::NOT_AUTHORIZED);
            } else {
                if (Hash::check($data['password'], $dbUser->password)) {
                    $token = $dbUser->createToken($deviceName)->plainTextToken;
                    $dbUser['access_token'] = $token;
                    return JsonResponse::respondSuccess(JsonResponse::MSG_LOGIN_SUCCESSFULLY, $dbUser);
                } else
                    return JsonResponse::respondError(JsonResponse::MSG_LOGIN_FAILED, ResponseStatus::NOT_AUTHORIZED);
            }
        } else {
            return JsonResponse::respondError($validator->errors()->all());
        }

    }

    public function loginBarber()
    {
        try {
            $rules = [
                'barber_code' => 'required',
                'password' => 'required'
            ];

            $validator = Validator::make($this->requestData, $rules, ValidatorHelper::messages());
            if ($validator->passes()) {

                $user = $this->barberRepository->findBy('barber_code', $this->requestData['barber_code']);
                
                if (!$user) {
                    return JsonResponse::respondError(JsonResponse::MSG_LOGIN_FAILED, ResponseStatus::VALIDATION_ERROR);
                }
                $barber = $user->where('password' , $this->requestData['password'])->first();
                
                if (!$barber) {
                    return JsonResponse::respondError(JsonResponse::MSG_LOGIN_FAILED, ResponseStatus::VALIDATION_ERROR);
                }
                $token = $user->createToken("barber")->plainTextToken;
                $user->access_token = $token;
                return JsonResponse::respondSuccess(trans(JsonResponse::MSG_LOGIN_SUCCESSFULLY), $user, 200);
            }
            return JsonResponse::respondError($validator->errors()->all(), ResponseStatus::VALIDATION_ERROR);
        } catch (\Exception $ex) {
            Log::info("exception" . $ex->getMessage());
            return JsonResponse::respondError($ex->getMessage());
        }
    }

    
    public function logoutBarber()
    {
        try {
            $user = Auth::guard('barber')->user();
            if($user){
                $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();
            return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS);
            }
            else{
               
               return JsonResponse::respondError("You are not login" );
            }
            
        } catch (\Exception $ex) {
            Log::debug($ex->getMessage());
            return JsonResponse::respondError("exception" . JsonResponse::MSG_FAILED);
        }

    }

    public function loginSalon()
    {
        try {
            $rules = [
                'salon_code' => 'required',
                'password' => 'required'
            ];

            $validator = Validator::make($this->requestData, $rules, ValidatorHelper::messages());
            if ($validator->passes()) {
                //$user = User::where('email', $request->email)->first();
                $salon = $this->salonRepository->findBy('salon_code', $this->requestData['salon_code']);
                $password = $salon->where('password' , $this->requestData['password'])->get();
               
                if (!$salon || !$password) {
                    return JsonResponse::respondError(JsonResponse::MSG_LOGIN_FAILED, ResponseStatus::VALIDATION_ERROR);
                }
                $token = $salon->createToken("barber")->plainTextToken;
                $user->access_token = $token;
                return JsonResponse::respondSuccess(trans(JsonResponse::MSG_LOGIN_SUCCESSFULLY), $salon, 200);
            }
            return JsonResponse::respondError($validator->errors()->all(), ResponseStatus::VALIDATION_ERROR);
        } catch (\Exception $ex) {
            Log::info("exception" . $ex->getMessage());
            return JsonResponse::respondError($ex->getMessage());
        }
    }

    //not yet
    public function logoutSalon()
    {
        try {
            $user = Auth::guard('salon')->user();
            if($user){
                $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();
            return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS);
            }
            else{
               
               return JsonResponse::respondError("You are not login" );
            }
            
        } catch (\Exception $ex) {
            Log::debug($ex->getMessage());
            return JsonResponse::respondError("exception" . JsonResponse::MSG_FAILED);
        }

    }


    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Kreait\Firebase\Exception\AuthException
     * @throws \Kreait\Firebase\Exception\FirebaseException
     */
    public function Register()
    {

        $data = $this->requestData;
        $validation_rules = [
            'phone' => 'required|unique:users,phone',
            'password' => 'required|confirmed|min:6',
            'country_id' => 'required|exists:countries,id',
            // 'salon_id' => 'required|exists:salons,id',
            'fcm_token' => 'required',
            'role' => 'required',
            'name' => 'required|string|max:30',
            'email' => 'email|unique:users',
        ];
        if (isset($data['yob']))
            $validation_rules['yob'] = 'date';

        if (isset($data['lang']))
            $data['lang'] = $this->requestData['lang'];

        if (isset($data['email']))
            $data['email'] = $this->requestData['email'];    

        $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());

        if ($validator->passes()) {
            $auth = $this->factory->createAuth();
            // // Retrieve the UID (User ID) from the verified Firebase credential's token
            $uid = $this->verifyToken($auth)->claims()->get('sub');
            $user = $auth->getUser($uid);
            // Retrieve the user model linked with the Firebase UID
            $data['firebase_uid'] = "jgjh99";
            if ($data['phone'] != $user->phoneNumber) {

                Log::error("register failed provided phone number not the same on the google firebase database");
                return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST, ResponseStatus::BAD_REQUEST);
            }
            $deviceName = isset($this->requestData['device_name']) ? $this->requestData['device_name'] : "access token";
            $data['fcm_token'] = $this->requestData['fcm_token'];
            $data['name'] = $this->requestData['name'];
            $data['password'] = Hash::make($data['password']);
            $data['salon_id'] = 0;

            // $user = new User();
            // $user->name = $data['name'] ;
            // $user->role = $data['role'];
            // $user->phone  = $data['phone'];
            // $user->password =  $data['password'];
            // $user->country_id  = $data['country_id'];
            // $user->salon_id = 22;
            // $user->yob = "2004-06-26 00:00:00";
            // $user->fcm_token = "dsds";
            // $user->lang = "en";
            // $user->email = "jhghgg@gmail.com";
         
            // $user->save() ;
            // return $user;

            $dbUser = $this->userRepository->create($data);
           
            // Create a Personnal Access Token
            $token = $dbUser->createToken($deviceName)->plainTextToken;
           
            // Store the created token
            $dbUser['access_token'] = $token;
            return JsonResponse::respondSuccess(JsonResponse::MSG_LOGIN_SUCCESSFULLY, $dbUser);
        } else {
            return JsonResponse::respondError($validator->errors()->all());

        }

    }


    /**
     * @return \Illuminate\Http\JsonResponse
     * @desc get authenticated user
     * @author samh 
     */
    public function getUser()
    {

        try {
            $user =$this->userRepository->find( Auth::guard('client')->id());
            // $user->append('workExperienceTitles');
            return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $user);
        } catch (\Exception $ex) {
            Log::info("exception" . $ex->getMessage());
            return JsonResponse::respondError($ex->getMessage());
        }

    }

    /**
     * logout and revoke user tokens
     * @return \Illuminate\Http\JsonResponse
     * @author Samh Dev
     */
    public function logout()
    {
        try {
            $user = Auth::guard('client')->user();
            $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();
            return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS);
        } catch (\Exception $ex) {
            Log::debug($ex->getMessage());
            return JsonResponse::respondError("exception" . JsonResponse::MSG_FAILED);
        }

    }

    /**
     * customize rules
     * @param $data
     * @return array
     * @author Samh Dev
     */
    public static function customizeRules($data)
    {
        $validation_rules = User::create_update_rules;
        return $validation_rules;
    }

    public function generateCode()
    {
        $code = "";
        while (true) {
            $code = Helpers::generateRandomString();
            if (!$this->userRepository->getAllInvitationCodes()->search($code))
                break;

        }
        return $code;
    }

    /**
     * @param $auth
     * @return mixed
     * @throws GeneralException
     */
    public function verifyToken(\Kreait\Firebase\Contract\Auth $auth)
    {
        // Retrieve the Firebase credential's token
        $idTokenString = $this->requestData['access_token'];
        try { // Try to verify the Firebase credential token with Google
            $verifiedIdToken = $auth->verifyIdToken($idTokenString);
            return $verifiedIdToken;
        } catch (InvalidMessage $e) { // If the token has the wrong format
            Log::debug($e->getMessage());
            throw new GeneralException(JsonResponse::MSG_BAD_REQUEST, JsonResponse::MSG_NOT_AUTHORIZED);
        }
    }

    
    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Kreait\Firebase\Exception\AuthException
     * @throws \Kreait\Firebase\Exception\FirebaseException
     */
    public function changePhone()
    {

        $dbUser = Auth::guard('client')->user();
        if($dbUser){

            $data = $this->requestData;
            $validation_rules = [
                'phone' => 'required|unique:users,phone',
                'access_token' => 'required',
            ];
              
            $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
    
            if ($validator->passes()) {
                $auth = $this->factory->createAuth();
                // Retrieve the UID (User ID) from the verified Firebase credential's token
                $uid = $this->verifyToken($auth)->claims()->get('sub');
                $user = $auth->getUser($uid);
    
                // Retrieve the user model linked with the Firebase UID
                $data['firebase_uid'] = $uid;
                if ($data['phone'] != $user->phoneNumber) {
    
                    Log::error("register failed provided phone number not the same on the google firebase database");
                    return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST, ResponseStatus::BAD_REQUEST);
                }
            
                $dbUser->phone = $data['phone'];
                $dbUser->save();
                
                $deviceName = isset($this->requestData['device_name']) ? $this->requestData['device_name'] : "access token";

                // Create a Personnal Access Token
                $token = $dbUser->createToken($deviceName)->plainTextToken;
                // Store the created token
                $dbUser['access_token'] = $token;
                return JsonResponse::respondSuccess(JsonResponse::MSG_LOGIN_SUCCESSFULLY, $dbUser);
            } 
            else {
                return JsonResponse::respondError($validator->errors()->all());
    
            }
        }
        
        else{
            return JsonResponse::respondError("not authenticate");
        }

    }


   
}

<?php

namespace App\Http\Controllers\API\Dashboard\Auth;

use App\Helpers\Mapper;
use App\Helpers\JsonResponse;
use App\Helpers\ResponseStatus;
use Illuminate\Http\Request;
use App\Helpers\ValidatorHelper;
use App\Http\Controllers\Controller;
use App\Http\Repositories\IRepositories\IAdminRepository;
use App\Http\Repositories\IRepositories\IBarberRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Repositories\IRepositories\IUserRepository;

/**
 * @OA\Post(
 * path="/api/admin/auth/login",
 * summary="Sign in",
 * description="Login by email, password",
 * tags={"Dashboard/Auth"},
 * @OA\RequestBody(
 *    required=true,
 *    description="Pass user credentials",
 *    @OA\JsonContent(
 *       required={"email","password"},
 *       @OA\Property(property="email", type="string", format="email", example="user1@mail.com"),
 *       @OA\Property(property="password", type="string", format="password", example="PassWord12345"),
 *    ),
 * ),
 * @OA\Response(
 *    response=422,
 *    description="Wrong credentials response",
 *    @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Sorry, wrong email address or password. Please try again")
 *        )
 *     )
 * )
 */

 /**
 * @OA\Post(
 * path="/api/admin/auth/logout",
 * summary="Logout",
 * description="Logout user and invalidate token",
 * tags={"Dashboard/Auth"},
 * security={ {"bearer": {} }},
 * @OA\Response(
 *    response=200,
 *    description="Success"
 *     ),
 * @OA\Response(
 *    response=401,
 *    description="Returns when user is not authenticated",
 *    @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Not authorized"),
 *    )
 * )
 * )
 */

class AuthController extends Controller
{
    protected $adminRepository;
    protected $barberRepository;
    protected $requestData;

    public function __construct(
        IAdminRepository $adminRepository,
        IBarberRepository $barberRepository
    )
    {
        $this->adminRepository = $adminRepository;
        $this->barberRepository = $barberRepository;
        $this->requestData = Mapper::toUnderScore(\Request()->all());
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @author samh
     * @desc login
     */
    public function login2()
    {
        try {
            $rules = [
                'email' => 'email|required',
                'password' => 'required'
            ];

            $validator = Validator::make($this->requestData, $rules, ValidatorHelper::messages());
            if ($validator->passes()) {
                $credentials = ["email" => $this->requestData['email'], "password" => $this->requestData['password']];
                if (Auth::guard('admin')->attempt($credentials)) {
                    Log::info(trans(JsonResponse::MSG_LOGIN_SUCCESSFULLY) . ": " . Auth::guard('admin')->user()->email);
                    return JsonResponse::respondSuccess(trans(JsonResponse::MSG_LOGIN_SUCCESSFULLY), Auth::guard('admin')->user(), 200);
                } else {
                    Log::info(trans(JsonResponse::MSG_LOGIN_FAILED) . ": " . $this->requestData['email']);
                    return JsonResponse::respondError(trans(JsonResponse::MSG_LOGIN_FAILED), ResponseStatus::NOT_AUTHORIZED);
                }
            }
            return JsonResponse::respondError($validator->errors()->all(), ResponseStatus::VALIDATION_ERROR);
        } catch (\Exception $ex) {
            Log::info("exception" . $ex->getMessage());
            return JsonResponse::respondError($ex->getMessage());
        }
    }

    /**
     * login using sanctum
     * @return \Illuminate\Http\JsonResponse
     * @author Samh Dev
     */
    public function login()
    {
        try {
            $rules = [
                'email' => 'email|required',
                'password' => 'required'
            ];

            $validator = Validator::make($this->requestData, $rules, ValidatorHelper::messages());
            if ($validator->passes()) {
                //$user = User::where('email', $request->email)->first();
                $user = $this->adminRepository->findBy('email', $this->requestData['email']);
                if (!$user || !Hash::check($this->requestData['password'], $user->password)) {
                    return JsonResponse::respondError(JsonResponse::MSG_LOGIN_FAILED, ResponseStatus::VALIDATION_ERROR);
                }
                $token = $user->createToken("admin")->plainTextToken;
                $user->access_token = $token;
                return JsonResponse::respondSuccess(trans(JsonResponse::MSG_LOGIN_SUCCESSFULLY), $user, 200);
            }
            return JsonResponse::respondError($validator->errors()->all(), ResponseStatus::VALIDATION_ERROR);
        } catch (\Exception $ex) {
            Log::info("exception" . $ex->getMessage());
            return JsonResponse::respondError($ex->getMessage());
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
            $user = Auth::guard('admin')->user();
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
    // public function logout()
    // {
    //     try {
    //         $user = Auth::guard('admin')->user();
    //         $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();
    //         return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS);
    //     } catch (\Exception $ex) {
    //         Log::debug($ex->getMessage());
    //         return JsonResponse::respondError("exception" . JsonResponse::MSG_FAILED);
    //     }

    // }

    public function logout () {

        $user = Auth::guard('admin')->user();
        $accessToken = $user->tokens()->where('id', $user->currentAccessToken()->id);

         if($accessToken){

            $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();
            return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS);
         }
         return JsonResponse::respondError("exception" . JsonResponse::MSG_FAILED);
        }

}

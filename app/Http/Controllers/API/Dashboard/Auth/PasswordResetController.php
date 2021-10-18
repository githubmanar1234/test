<?php

namespace App\Http\Controllers\API\Dashboard\Auth;

use App\Helpers\Mapper;
use App\Http\Repositories\IRepositories\IAdminRepository;
use App\Http\Repositories\IRepositories\IPasswordResetRepository;
use App\Jobs\SendEmail;
use App\Mail\ResetPassword;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;


class PasswordResetController extends Controller
{
    private $requestData;
    private $adminRepository;
    private $passwordResetRepository;

    public function __construct(IPasswordResetRepository $passwordResetRepository, IAdminRepository $adminRepository)
    {
        $this->adminRepository = $adminRepository;
        $this->passwordResetRepository = $passwordResetRepository;
        $this->requestData = Mapper::toUnderScore((\Request()->all()));
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResetToken()
    {
        try {
            $validator = Validator::make($this->requestData, ['email' => 'required|email']);
            if ($validator->passes()) {
                $email = $this->requestData['email'];
                $admin = $this->adminRepository->findBy('email', $email);
                if (!$admin) return JsonResponse::respondError(JsonResponse::MSG_USER_NOT_FOUND);
                $passwordReset = $this->passwordResetRepository->updateOrCreate(
                    'email', $email,
                    [
                        'email' => $email,
                        'reset_code' => Str::random(6)
                    ]
                );
                if ($passwordReset) {
                    $passReset = $this->passwordResetRepository->findBy('email', $email);
                    $name = $admin->name;
                    $resetCode = $passReset->reset_code;
                    $resetMail = new ResetPassword([
                        'name' => $name,
                        'resetCode' => $resetCode,
                        'email' => $email
                    ]);
                    SendEmail::dispatch($resetMail, $email);
                    Log::info("reset email sent to " . $email . " at " . Carbon::now());
                    return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS);
                }
            }
            return JsonResponse::respondError($validator->errors()->all());
        } catch (\Exception $exception) {
            return JsonResponse::respondError($exception->getMessage());
        }

    }


    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function reset()
    {
        try {
            $rules = [
                'email' => 'required',
                'password' => 'required|string',
                'password_confirmation' => 'required|same:password',
                'reset_code' => 'required|string'
            ];

            $validator = Validator::make($this->requestData, $rules);
            if ($validator->passes()) {
                $passwordReset = $this->passwordResetRepository->findBy('email', $this->requestData['email']);
                if (!$passwordReset || $passwordReset->reset_code != $this->requestData['reset_code'])
                    return JsonResponse::respondError(trans(JsonResponse::MSG_CODE_INVALID));
                $user = $this->adminRepository->findBy('email', $this->requestData['email']);
                if (!$user) return JsonResponse::respondError(trans(JsonResponse::MSG_USER_NOT_FOUND));
                $this->adminRepository->update(['password' => bcrypt($this->requestData['password'])], $user->id);
                $this->passwordResetRepository->delete($passwordReset);
                Log::info(trans(JsonResponse::MSG_PASSWORD_RESET));
                return JsonResponse::respondSuccess(trans(JsonResponse::MSG_SUCCESS));
            } else {
                return JsonResponse::respondError($validator->errors());
            }
        } catch (\Exception $exception) {
            return JsonResponse::respondError($exception->getMessage());
        }

    }

}

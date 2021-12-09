<?php

namespace App\Http\Controllers\API\Client;

use App\Helpers\Constants;
use App\Helpers\JsonResponse;
use App\Helpers\Mapper;
use App\Http\Controllers\Controller;
use App\Http\Repositories\IRepositories\ICategoryRepository;
use App\Http\Repositories\IRepositories\ISalonRepository;
use App\Http\Repositories\IRepositories\IUserRepository;
use App\Http\Repositories\IRepositories\IBarberRepository;
use App\Http\Repositories\IRepositories\IBarberReportRepository;
use App\Http\Repositories\IRepositories\ISalonReportRepository;
use App\Http\Repositories\IRepositories\IPostReportRepository;
use App\Models\Category;
use App\Models\Salon;
use App\Models\Barber;
use App\Models\Setting;
use App\Models\Post;
use App\Models\PostReport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ValidatorHelper;


class ReportController extends Controller
{
    private $userRepository;
    private $categoryRepository;
    private $salonRepository;
    private $barberRepository;
    private $barberReportRepository;
    private $salonReportRepository;
    private $postReportRepository;
    private $requestData;
    private $authUser;

    public function __construct(
        ICategoryRepository $categoryRepository,
        ISalonReportRepository $salonReportRepository,
        IPostReportRepository $postReportRepository,
        IUserRepository $userRepository,
        ISalonRepository $salonRepository,
        IBarberReportRepository $barberReportRepository,
        IBarberRepository $barberRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->barberReportRepository = $barberReportRepository;
        $this->salonReportRepository = $salonReportRepository;
        $this->postReportRepository = $postReportRepository;
        $this->categoryRepository = $categoryRepository;
        $this->barberRepository = $barberRepository;
        $this->salonRepository = $salonRepository;
        $this->requestData = Mapper::toUnderScore(\Request()->all());
        $this->authUser = Auth::guard('client')->user();
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

    //Report barber 
    public function reportBarber(Request $request)
    {
       $user = Auth::guard('client')->user();
       if($user){
                $data = $this->requestData;
                $validation_rules = [
                    'reason' => "required",
                    'barber_id' => "required|exists:barbers,id",   
                ];
                $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
                if ($validator->passes()) {

                    $dataBarber = $this->barberRepository->allAsQuery();

                    $dataBarber = $dataBarber->find($data['barber_id']);

                    if($dataBarber){

                        if($dataBarber->status == Constants::STATUS_ACCEPTED){
            
                            $data['user_id'] = $user->id;
                            $resource = $this->barberReportRepository->create($data);
                        
                            if (!$resource) return JsonResponse::respondError(JsonResponse::MSG_CREATION_ERROR);
                            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY), $resource);
                        }  
                        else{
                            return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
                            } 
                    }
                    else{
                        return JsonResponse::respondError("Barber does not exist");
                        } 
                    
                }
                return JsonResponse::respondError($validator->errors()->all());
        }
        else{
            return JsonResponse::respondError("You are not user");
        }
    }

    //Report salon 
    public function reportSalon(Request $request)
    {
       $user = Auth::guard('client')->user();
       if($user){
            $data = $this->requestData;
            $validation_rules = [
                'reason' => "required",
                'salon_id' => "required|exists:salons,id",   
            ];
            $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
            if ($validator->passes()) {

                $dataSalon = $this->salonRepository->allAsQuery();

                $dataSalon = $dataSalon->find($data['salon_id']);

                if($dataSalon){

                    if($dataSalon->status == Constants::STATUS_ACCEPTED){
        
                        $data['user_id'] = $user->id;
                        $resource = $this->salonReportRepository->create($data);
                    
                        if (!$resource) return JsonResponse::respondError(JsonResponse::MSG_CREATION_ERROR);
                        return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY), $resource);
                    }  
                    else{
                        return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
                        } 
                }
                else{
                    return JsonResponse::respondError("Salon does not exist");
                    } 
                
            }
            return JsonResponse::respondError($validator->errors()->all());
        }
        else{
            return JsonResponse::respondError("You are not user");
        }

    }

    //Report post
    public function reportPost(Request $request)
    {
        
       $user = Auth::guard('client')->user();
        if($user){
                $data = $this->requestData;
                $validation_rules = [
                    'reason' => "required",
                    'post_id' => "required|exists:posts,id",   
                ];
                $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
                if ($validator->passes()) {

                    $dataPost= $this->postRepository->allAsQuery();

                    $dataPost = $dataPost->find($data['post_id']);

                    if($dataPost){
                        $data['user_id'] = $user->id;
                    
                        $resource = $this->postReportRepository->create($data);
                        if (!$resource) return JsonResponse::respondError(JsonResponse::MSG_CREATION_ERROR);
                    }
                    else{
                            return JsonResponse::respondError("Post does not exist");    
                    }
                   

                    $value = Setting::where('key' , "number of reported post for deletion")->first()->value;
                    
                    $numOfReports = PostReport::where('post_id' , $data['post_id'])->count();
                    
                    if($numOfReports >= $value){
                        Post::where('id' ,  $data['post_id'])->delete();
                    }

                    return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY), $resource);
                    
                
                    
                }
                return JsonResponse::respondError($validator->errors()->all());
        }
        else{
            return JsonResponse::respondError("You are not user");
        }        
    }


  
}
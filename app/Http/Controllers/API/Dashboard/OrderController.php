<?php

namespace App\Http\Controllers\API\Dashboard;

use App\Helpers\Constants;
use App\Helpers\JsonResponse;
use App\Helpers\Mapper;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Http\Repositories\IRepositories\ICategoryRepository;
use App\Http\Repositories\IRepositories\IPostRepository;
use App\Http\Repositories\IRepositories\IUserRepository;
use App\Http\Repositories\IRepositories\ISalonRepository;
use App\Http\Repositories\IRepositories\IPostImageRepository;
use App\Http\Repositories\IRepositories\IPostLikeRepository;
use App\Http\Repositories\IRepositories\IBarberRepository;
use App\Http\Repositories\IRepositories\IOrderRepository;
use App\Models\Category;
use App\Models\Salon;
use App\Models\Barber;
use App\Models\BarberImage;
use App\Models\Post;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Image;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ValidatorHelper;
use App\Helpers\FileHelper;


class OrderController extends Controller
{
    private $userRepository;
    private $categoryRepository;
    private $orderRepository;
    private $barberRepository;
    private $postImageRepository;
    private $requestData;
    private $authUser;

    public function __construct(
        ICategoryRepository $categoryRepository,
        IUserRepository $userRepository,
        IOrderRepository $orderRepository,
        ISalonRepository $salonRepository,
        IPostImageRepository $postImageRepository,
        IPostLikeRepository $postLikeRepository,
        IBarberRepository $barberRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->orderRepository = $orderRepository;
        $this->salonRepository = $salonRepository;
        $this->barberRepository = $barberRepository;
        $this->postLikeRepository = $postLikeRepository;
        $this->postImageRepository = $postImageRepository;
        $this->requestData = Mapper::toUnderScore(\Request()->all());
        //$this->authBarber = Auth::guard('barber')->user();
        $this->authUser = Auth::guard('admin')->user();
    }


    //get daily orders for barbers
    public function getOrders(){

        $request_data = $this->requestData;

        $user = Auth::guard('admin')->user();
        
        if($user){
            
            // $data = Order::where('status' , Constants::ORDER_STATUS_ACCEPTED)
            // ->orWhere('status' , Constants::ORDER_STATUS_COMPLETED)->get();
            $data = Order::all();
            return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
        }

        return JsonResponse::respondError("You are not admin");  
    }


    

     

}
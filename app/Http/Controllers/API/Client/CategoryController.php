<?php

namespace App\Http\Controllers\API\Client;

use App\Helpers\Constants;
use App\Helpers\JsonResponse;
use App\Helpers\Mapper;
use App\Http\Controllers\Controller;
use App\Http\Repositories\IRepositories\ICategoryRepository;
use App\Http\Repositories\IRepositories\IUserRepository;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;


class CategoryController extends Controller
{
    private $userRepository;
    private $categoryRepository;
    private $requestData;
    private $authUser;

    public function __construct(
        ICategoryRepository $categoryRepository,
        IUserRepository $userRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->categoryRepository = $categoryRepository;
        $this->requestData = Mapper::toUnderScore(\Request()->all());
        $this->authUser = Auth::guard('client')->user();
    }

    public function categories(){
        $data = $this->categoryRepository->allAsQuery()->get();
        return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS,$data);
    }

    // /**
    //  * get subcategory by categoryId
    //  * @param Category $category
    //  * @return \Illuminate\Http\JsonResponse
    //  */
    // public function subcategories(Category $category){
    //     if (isset($category->parent_id))
    //         return JsonResponse::respondError(trans(JsonResponse::MSG_BAD_REQUEST));
    //     $data = $this->categoryRepository->allAsQuery()->where("parent_id",$category->id)->get();
    //     return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS,$data);
    // }
    /**
     * find subcategory by categoryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function find(){

        $request_data = $this->requestData;

        $data = $this->categoryRepository->allAsQuery();
        
        if (isset($this->requestData['title']))foreach (Constants::LANGUAGES as $LANGUAGE){
            $data = $data->orWhere("title->".$LANGUAGE,'like',"%".lcfirst($request_data['title'])."%");
            $data = $data->orWhere("title->".$LANGUAGE,'like',"%".ucfirst($request_data['title'])."%");
        }
        if (isset($this->requestData['description'])) foreach (Constants::LANGUAGES as $LANGUAGE) {
            $data = $data->orWhere("description->" . $LANGUAGE, 'like', "%".$request_data['description']."%");
        }
        $data = $data->get();
        return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS,$data);
    }



}
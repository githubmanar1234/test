<?php

namespace App\Http\Controllers\API\Client;

use App\Helpers\Constants;
use App\Helpers\JsonResponse;
use App\Helpers\Mapper;
use App\Http\Controllers\Controller;
use App\Http\Repositories\IRepositories\ICategoryRepository;
use App\Http\Repositories\IRepositories\IPostRepository;
use App\Http\Repositories\IRepositories\IUserRepository;
use App\Http\Repositories\IRepositories\ISalonRepository;
use App\Http\Repositories\IRepositories\IPostImageRepository;
use App\Http\Repositories\IRepositories\IPostLikeRepository;
use App\Http\Repositories\IRepositories\IBarberRepository;
use App\Models\Category;
use App\Models\Salon;
use App\Models\Barber;
use App\Models\Post;
use App\Models\PostImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Image;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ValidatorHelper;
use App\Helpers\FileHelper;

class PostController extends Controller
{
    private $userRepository;
    private $categoryRepository;
    private $postRepository;
    private $barberRepository;
    private $postImageRepository;
    private $requestData;
    private $authUser;

    public function __construct(
        ICategoryRepository $categoryRepository,
        IUserRepository $userRepository,
        IPostRepository $postRepository,
        ISalonRepository $salonRepository,
        IPostImageRepository $postImageRepository,
        IPostLikeRepository $postLikeRepository,
        IBarberRepository $barberRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->postRepository = $postRepository;
        $this->salonRepository = $salonRepository;
        $this->postLikeRepository = $postLikeRepository;
        $this->postImageRepository = $postImageRepository;
        $this->requestData = Mapper::toUnderScore(\Request()->all());
        $this->authUser = Auth::guard('client')->user();
    }


    public function index()
    {
        $data = $this->postRepository->all();
        if (!$data) return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
        return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
    }

    //create posts
    public function store(Request $request)
    {
    
        $user = Auth::guard('client')->user();
    
        $data = $this->requestData;

        $validation_rules = [
            'description' => "required",
            'salon_id' => "required",
        ];

        $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());

        if ($validator->passes()) {

            if(!$request->hasFile('images')) {
                return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
            }

            $salon_id = $data['salon_id'];
           
            $resource = Salon::find($salon_id);
            if (!$resource) return JsonResponse::respondError(JsonResponse::MSG_CREATION_ERROR);

            $data['published_at'] = Carbon::now();

            $resource = $this->postRepository->create($data);

            if (!$resource) return JsonResponse::respondError(JsonResponse::MSG_CREATION_ERROR);

            $files = $request->file('images'); 

            foreach ($files as $file) {      

                $imageUrl = FileHelper::processImage($file, 'public/posts');
                $postImage = new PostImage();
                $postImage->image = $imageUrl;
                $postImage->post_id = $resource->id;
                $postImage->save();
            }

            if (!$resource) return JsonResponse::respondError(JsonResponse::MSG_CREATION_ERROR);

            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY), $resource);
         
        }

        return JsonResponse::respondError($validator->errors()->all());
    }


    //update post by his owner
    public function update(Request $request) 
    {

        $user = Auth::guard('client')->user();
           
        $salon_id = $user->salon->first()->id;
          
        $data = $this->requestData;

        $validation_rules = [
          'post_id' => "required",
           
        ];
    
        $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
    
        if ($validator->passes()) {
              
            $resource = Post::find($data['post_id']);
                
            if($resource){

                if ($resource->salon_id != $salon_id ){

                    return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
                }
                if (isset($data['description'])){
                
                    $resource->description = $data['description'];
                }
                $resource->save();

            
                if($request->hasFile('images')) {
                    if (isset($data['images'])){

                        $files = $request->file('images'); 
                        foreach ($files as $file) {      
                            PostImage::where('post_id' , $resource->id)->delete();
                            $imageUrl = FileHelper::processImage($file, 'public/posts');
                            $postImage = new PostImage();
                            $postImage->image = $imageUrl;
                            $postImage->post_id = $resource->id;
                            $postImage->save();
                        }
                    }
                }    

                $updated = $this->postRepository->update($data, $resource->id);
                if (!$updated) return JsonResponse::respondError(trans(JsonResponse::MSG_UPDATE_ERROR));
                return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));
            }

            else{
                return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
            }
        }
        return JsonResponse::respondError($validator->errors()->all());


    }
  


    public function likePost(Request $request)
    {
    
        $user = Auth::guard('client')->user();
    
        $data = $this->requestData;
        $validation_rules = [
            'post_id' => "required",
        ];
        $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
        if ($validator->passes()) {

           $post_id = $data['post_id'];
           $post = Post::find($post_id);
        
           if(!$post){
            return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
           }
           else{
            
            $data['user_id'] = $user->id;
            $resource = $this->postLikeRepository->create($data);

            if (!$resource) return JsonResponse::respondError(JsonResponse::MSG_CREATION_ERROR);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY), $resource);
           }  
            
           
         }
        return JsonResponse::respondError($validator->errors()->all());
    }

    //delete post by its salon
    public function destroy($id)
    {

        $user = Auth::guard('client')->user();

        $salon_id = $user->salon->first()->id;

        $resource = Post::find($id);
        $resource->where('salon_id' , $salon_id);

        if($resource ){

            $this->postRepository->delete($resource);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        }
        return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);    
    }
  
}
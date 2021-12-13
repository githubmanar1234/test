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
use App\Http\Repositories\IRepositories\IViewRepository;
use App\Models\Category;
use App\Models\Salon;
use App\Models\Barber;
use App\Models\View;
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
    private $viewRepository;
    private $requestData;
    private $authUser;

    public function __construct(
        ICategoryRepository $categoryRepository,
        IViewRepository $viewRepository,
        IUserRepository $userRepository,
        IPostRepository $postRepository,
        ISalonRepository $salonRepository,
        IPostImageRepository $postImageRepository,
        IPostLikeRepository $postLikeRepository,
        IBarberRepository $barberRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->viewRepository = $viewRepository;
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
    
        $data = $this->requestData;

        $validation_rules = [
            'description' => "required",
        ];

        $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());

        if ($validator->passes()) {

                $user = Auth::guard('client')->user();
                 
                if($user->salon){

                    $salon = $user->salon;

                    $salon_id = $salon->id;

                    if($salon->status == Constants::STATUS_ACCEPTED){

                        if(!$request->hasFile('images')) {
                                return JsonResponse::respondError("You must insert one image at least");
                        }
    
                        $data['published_at'] = Carbon::now();
                        $data['salon_id'] = $salon_id;
                        $data['description'] = $this->requestData['description'];
    
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
                    else{
                        return JsonResponse::respondError("Your salon not accepted yet");
                    } 
                }
                else{
                    return JsonResponse::respondError("You dont have salon");
                }   
        }

        return JsonResponse::respondError($validator->errors()->all());
    }

    //update post by his owner
    public function update(Request $request) 
    {

        $user = Auth::guard('client')->user(); 
      
        $data = $this->requestData;

        $validation_rules = [
          'post_id' => "required|exists:posts,id",    
        ];
    
        $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
    
        if ($validator->passes()) {

            if($user->salon){

                $salon = $user->salon;
                $salon_id = $salon->id;

                if($salon->status == Constants::STATUS_ACCEPTED){

                    $post = Post::where('salon_id' , $salon_id)->where('id' ,$data['post_id'])->first();

                    if (!$post ){

                        return JsonResponse::respondError("You are not owner on this post");
                    }

                    if (isset($data['description'])){
                    
                        $post->description = $data['description'];
                    }
                    $post->save();
                
                    if($request->hasFile('images')) {
                        if (isset($data['images'])){

                            $files = $request->file('images'); 
                            foreach ($files as $file) {      
                                PostImage::where('post_id' , $post->id)->delete();
                                $imageUrl = FileHelper::processImage($file, 'public/posts');
                                $postImage = new PostImage();
                                $postImage->image = $imageUrl;
                                $postImage->post_id = $post->id;
                                $postImage->save();
                            }
                        }
                    }    

                    $updated = $this->postRepository->update($data, $post->id);
                    if (!$updated) return JsonResponse::respondError(trans(JsonResponse::MSG_UPDATE_ERROR));
                    return JsonResponse::respondSuccess(trans(JsonResponse::MSG_UPDATED_SUCCESSFULLY));  
                } 
                else{
                    return JsonResponse::respondError("Your salon not accepted");
                } 
         } 
         else{
            return JsonResponse::respondError("You dont have salon");
          }   

        }
        return JsonResponse::respondError($validator->errors()->all());
    }
  

    public function likePost(Request $request)
    {
    
        $user = Auth::guard('client')->user();
        if($user){
            $data = $this->requestData;
            $validation_rules = [
                'post_id' => "required|exists:posts,id",
            ];
            
            $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
            if ($validator->passes()) {

            $post_id = $data['post_id'];
            $post = Post::find($post_id);
            
                $data['user_id'] = $user->id;
                $resource = $this->postLikeRepository->create($data);

                if (!$resource) return JsonResponse::respondError(JsonResponse::MSG_CREATION_ERROR);
                return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY), $resource);
            }
            return JsonResponse::respondError($validator->errors()->all());
        }
        else{
            return JsonResponse::respondError("You are not user");
        }
           
    }

    //delete post by its salon
    public function destroy($id)
    {

        $user = Auth::guard('client')->user();

        if($user->salon){

            $salon_id = $user->salon->id;

            $salon = $user->salon;

            if($salon->status == Constants::STATUS_ACCEPTED){

                $postExist = Post::find($id);

                if(!$postExist){

                    if (is_numeric($id)){
                        return JsonResponse::respondError(JsonResponse::MSG_NOT_FOUND);
                    }
                    return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST); 
                }

                $post = Post::where('salon_id' , $salon_id)->where('id' ,$id )->first();

                if($post){

                    $this->postRepository->delete($post);
                    return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));       
                }
                    
                else{
                    return JsonResponse::respondError("You are not owner on this post");
                }  
            
            }
            else{
                return JsonResponse::respondError("Your salon not accepted");
            }
      }
      else{
        return JsonResponse::respondError("You dont have salon ");
      }
    } 

    public function viewPost(Request $request)
    {
        $user = Auth::guard('client')->user();

        if($user){

            $data = $this->requestData;

            $validation_rules = [
                'post_id' => "required|exists:posts,id",
            ];
            
            $validator = Validator::make($data, $validation_rules, ValidatorHelper::messages());
            if ($validator->passes()) {

                    $views = View::where('user_id' , $user->id)->where('post_id' , $data['post_id'])->get();

                    if(count($views) > 0){
                        foreach($views as $view){

                            $now = Carbon::now()->day;
                            $created_at =Carbon::parse($view->created_at)->day;

                            if($created_at !== $now){

                                $data['user_id'] = $user->id;
                        
                                $resource = $this->viewRepository->create($data);
            
                                if (!$resource) return JsonResponse::respondError(JsonResponse::MSG_CREATION_ERROR);
                                return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY), $resource);
                            }
                            else{
                            return JsonResponse::respondError("You already have viewed this post today");
                            }
                        }
                    }
                    else{
                        $data['user_id'] = $user->id;
                        
                        $resource = $this->viewRepository->create($data);
    
                        if (!$resource) return JsonResponse::respondError(JsonResponse::MSG_CREATION_ERROR);
                        return JsonResponse::respondSuccess(trans(JsonResponse::MSG_ADDED_SUCCESSFULLY), $resource);
                        }
                     
                   
            }
            return JsonResponse::respondError($validator->errors()->all());
        }
        else{
            return JsonResponse::respondError("You are not user");
        }
           
    }
  
}
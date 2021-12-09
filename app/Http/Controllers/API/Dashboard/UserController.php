<?php

namespace App\Http\Controllers\API\Dashboard;

use App\Exceptions\GeneralException;
use App\Helpers\Constants;
use App\Helpers\JsonResponse;
use App\Helpers\Mapper;
use App\Helpers\ValidatorHelper;
use App\Http\Controllers\Controller;
use App\Http\Repositories\Eloquent\InvitationRepository;
use App\Http\Repositories\IRepositories\ICountryRepository;
use App\Http\Repositories\IRepositories\IInvitationRepository;
use App\Http\Repositories\IRepositories\IPollingRepository;
use App\Http\Repositories\IRepositories\ITicketRepository;
use App\Http\Repositories\IRepositories\IUserRepository;
use App\Jobs\HandleGoldenPolling;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    private $userRepository;
    private $countryRepository;
    private $requestData;

    public function __construct(
        IUserRepository $userRepository,
        ICountryRepository $countryRepository)
    {
        $this->userRepository = $userRepository;
        $this->countryRepository = $countryRepository;
        $this->requestData = Mapper::toUnderScore(\Request()->all());
    }

    /**
     * gey resource list
     * @return \Illuminate\Http\JsonResponse
     * @author samh
     */
    public function index()
    {
        $data = $this->userRepository->all();
      
        if (!$data) return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
        return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
    }


    /**
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     * @author Samh Dev
     */

    public function show($id)
    {
        $user = User::find($id);

        if($user){
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_SUCCESS), $user);
        }
        else{
            if (is_numeric($id)){
                return JsonResponse::respondError(JsonResponse::MSG_USER_NOT_FOUND);
            }

            return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
        }  
    }
    

    /**
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     * @author Samh Dev
     */

    public function destroy($id)
    {
        $resource = User::find($id);

        if($resource){

            $this->userRepository->delete($resource);
            return JsonResponse::respondSuccess(trans(JsonResponse::MSG_DELETED_SUCCESSFULLY));
        }

         else{
             
            if (is_numeric($id)){
                return JsonResponse::respondError(JsonResponse::MSG_USER_NOT_FOUND);
            }

            return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
        }  
    
    }

    public function updateUserRole(Request $request) 
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
}

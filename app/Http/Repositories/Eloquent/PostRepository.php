<?php

namespace App\Http\Repositories\Eloquent;
use App\Http\Repositories\IRepositories\IPostRepository;
use App\Models\Post;

class PostRepository extends BaseRepository implements IPostRepository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return Post::class;
    }


    /**
     * get Posts who has salon Reports
     * @return [Post]
     */
    public function reportedPosts()
    {
        $reportedPosts = $this->model->whereHas('postReports', function ($q)  {
        })->orderBy('id', 'desc')->with('postReports')->get();

        return $reportedPosts;
    }

}

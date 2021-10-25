<?php

namespace App\Http\Repositories\Eloquent;
use App\Http\Repositories\IRepositories\IPostLikeRepository;
use App\Models\PostLike;

class PostLikeRepository extends BaseRepository implements IPostLikeRepository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return PostLike::class;
    }


    /**
     * get salons who has salon Reports
     * @return [Salon]
     */
    // public function reportedSalons()
    // {
    //     $reportedSalons = $this->model->whereHas('salonReports', function ($q)  {
    //     })->orderBy('id', 'desc')->with('salonReports')->get();

    //     return $reportedSalons;
    // }

}

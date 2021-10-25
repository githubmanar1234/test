<?php

namespace App\Http\Repositories\Eloquent;
use App\Http\Repositories\IRepositories\IPostImageRepository;
use App\Models\PostImage;

class PostImageRepository extends BaseRepository implements IPostImageRepository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return PostImage::class;
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

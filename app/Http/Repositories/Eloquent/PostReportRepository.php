<?php

namespace App\Http\Repositories\Eloquent;
use App\Http\Repositories\IRepositories\IServiceRepository;
use App\Http\Repositories\IRepositories\IPostReportRepository;
use App\Models\SalonReport;

class PostReportRepository extends BaseRepository implements IPostReportRepository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return PostReport::class;
    }


}

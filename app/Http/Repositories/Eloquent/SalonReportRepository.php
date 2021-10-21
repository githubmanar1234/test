<?php

namespace App\Http\Repositories\Eloquent;
use App\Http\Repositories\IRepositories\IServiceRepository;
use App\Http\Repositories\IRepositories\ISalonReportRepository;
use App\Models\SalonReport;

class SalonReportRepository extends BaseRepository implements ISalonReportRepository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return SalonReport::class;
    }


}

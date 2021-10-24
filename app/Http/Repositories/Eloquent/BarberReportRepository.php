<?php

namespace App\Http\Repositories\Eloquent;
use App\Http\Repositories\IRepositories\IServiceRepository;
use App\Http\Repositories\IRepositories\IBarberReportRepository;
use App\Models\BarberReport;

class BarberReportRepository extends BaseRepository implements IBarberReportRepository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return BarberReport::class;
    }


}

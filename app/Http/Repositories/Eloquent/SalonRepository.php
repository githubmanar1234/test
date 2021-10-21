<?php

namespace App\Http\Repositories\Eloquent;
use App\Http\Repositories\IRepositories\ISalonRepository;
use App\Models\Salon;

class SalonRepository extends BaseRepository implements ISalonRepository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return Salon::class;
    }


    /**
     * get salons who has salon Reports
     * @return [Salon]
     */
    public function reportedSalons()
    {
        $reportedSalons = $this->model->whereHas('salonReports', function ($q)  {
        })->orderBy('id', 'desc')->with('salonReports')->get();

        return $reportedSalons;
    }

}

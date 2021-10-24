<?php

namespace App\Http\Repositories\Eloquent;
use App\Http\Repositories\IRepositories\IBarberRepository;
use App\Models\Barber;

class BarberRepository extends BaseRepository implements IBarberRepository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return Barber::class;
    }


    public function reportedBarbers()
    {
        $reportedBarbers = $this->model->whereHas('barberReports', function ($q)  {
        })->orderBy('id', 'desc')->with('barberReports')->get();

        return $reportedBarbers;
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

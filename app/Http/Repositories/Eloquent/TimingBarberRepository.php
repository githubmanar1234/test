<?php
namespace App\Http\Repositories\Eloquent;



use App\Http\Repositories\IRepositories\ITimingBarberRepository;
use App\Models\TimingBarber;

class TimingBarberRepository extends BaseRepository implements ITimingBarberRepository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return TimingBarber::class;
    }


}

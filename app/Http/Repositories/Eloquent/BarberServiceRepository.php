<?php
namespace App\Http\Repositories\Eloquent;



use App\Http\Repositories\IRepositories\IBarberServiceRepository;
use App\Models\BarberService;

class BarberServiceRepository extends BaseRepository implements IBarberServiceRepository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return BarberService::class;
    }


}

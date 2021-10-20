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


}

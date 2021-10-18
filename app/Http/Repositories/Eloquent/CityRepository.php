<?php

namespace App\Http\Repositories\Eloquent;

use App\Http\Repositories\IRepositories\ICityRepository;
use App\Models\City;

class CityRepository extends BaseRepository implements ICityRepository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return City::class;
    }


}

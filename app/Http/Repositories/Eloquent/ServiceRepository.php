<?php

namespace App\Http\Repositories\Eloquent;
use App\Http\Repositories\IRepositories\IServiceRepository;
use App\Models\Service;

class ServiceRepository extends BaseRepository implements IServiceRepository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return Service::class;
    }


}

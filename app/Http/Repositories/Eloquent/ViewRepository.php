<?php

namespace App\Http\Repositories\Eloquent;
use App\Http\Repositories\IRepositories\IServiceRepository;
use App\Http\Repositories\IRepositories\IViewRepository;
use App\Models\View;

class ViewRepository extends BaseRepository implements IViewRepository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return View::class;
    }


}

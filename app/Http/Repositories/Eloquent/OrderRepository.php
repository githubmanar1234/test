<?php
namespace App\Http\Repositories\Eloquent;



use App\Http\Repositories\IRepositories\IOrderRepository;
use App\Models\Admin;

class OrderRepository extends BaseRepository implements IOrderRepository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return Admin::class;
    }


}

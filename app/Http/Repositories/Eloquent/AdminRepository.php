<?php
namespace App\Http\Repositories\Eloquent;



use App\Http\Repositories\IRepositories\IAdminRepository;
use App\Models\Admin;

class AdminRepository extends BaseRepository implements IAdminRepository
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

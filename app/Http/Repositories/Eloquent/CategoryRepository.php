<?php

namespace App\Http\Repositories\Eloquent;



use App\Http\Repositories\IRepositories\ICategoryRepository;

use App\Models\Category;

class CategoryRepository extends BaseRepository implements ICategoryRepository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return Category::class;
    }


}

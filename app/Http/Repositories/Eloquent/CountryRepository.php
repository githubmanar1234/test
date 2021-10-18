<?php

namespace App\Http\Repositories\Eloquent;


use App\Http\Repositories\IRepositories\ICountryRepository;
use App\Models\Country;
use Illuminate\Support\Facades\DB;

class CountryRepository extends BaseRepository implements ICountryRepository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return Country::class;
    }

    /**
     * get top user who complete max tickets count
     * @param $from
     * @param $to
     * @return mixed
     */
    public function topCountries($from, $to)
    {

        $query = $this->model->select('countries.*', DB::raw('COUNT( distinct users.id) As users_count'))
            ->join('users', 'users.country_id', '=', 'countries.id')
            ->join('tickets', 'tickets.user_id', '=', 'users.id');
        $query->whereDate('tickets.created_at', '>=', $from);
        $query->whereDate('tickets.created_at', '<=', $to);
        return $query->groupBy('countries.id')->orderBy(DB::raw('COUNT(distinct tickets.id)'))->get();
    }


}

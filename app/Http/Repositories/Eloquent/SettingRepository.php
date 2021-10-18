<?php
namespace App\Http\Repositories\Eloquent;



use App\Http\Repositories\IRepositories\ICardRepository;
use App\Http\Repositories\IRepositories\ISettingRepository;
use App\Models\Card;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class SettingRepository extends BaseRepository implements ISettingRepository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return Setting::class;
    }




}

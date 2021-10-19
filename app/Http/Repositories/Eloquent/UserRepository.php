<?php

namespace App\Http\Repositories\Eloquent;

use App\Exceptions\GeneralException;
use App\Helpers\Constants;
use App\Helpers\JsonResponse;
use App\Http\Repositories\IRepositories\IUserRepository;
use App\Models\AdsTask;
use App\Models\Invitation;
use App\Models\Polling;
use App\Models\SocialMediaTask;
use App\Models\User;
use App\Models\UserSocialMediaTask;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class UserRepository extends BaseRepository implements IUserRepository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return User::class;
    }


}

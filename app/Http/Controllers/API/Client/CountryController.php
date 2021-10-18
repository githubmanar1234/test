<?php

namespace App\Http\Controllers\API\Client;

use App\Exceptions\GeneralException;
use App\Helpers\Constants;
use App\Helpers\JsonResponse;
use App\Helpers\Mapper;
use App\Helpers\ValidatorHelper;
use App\Http\Controllers\Controller;
use App\Http\Repositories\Eloquent\InvitationRepository;
use App\Http\Repositories\IRepositories\ICityRepository;
use App\Http\Repositories\IRepositories\ICountryRepository;
use App\Http\Repositories\IRepositories\IInvitationRepository;
use App\Http\Repositories\IRepositories\IPollingRepository;
use App\Http\Repositories\IRepositories\ITicketRepository;
use App\Http\Repositories\IRepositories\IUserRepository;
use App\Jobs\HandleInvitation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CountryController extends Controller
{
    private $countryRepository;
    private $cityRepository;
    private $requestData;
    private $authUser;

    public function __construct(
        ICountryRepository $countryRepository, ICityRepository $cityRepository
    )
    {
        $this->countryRepository = $countryRepository;
        $this->cityRepository = $cityRepository;
        $this->requestData = Mapper::toUnderScore(\Request()->all());
        $this->authUser = Auth::guard('client')->user();
    }


    /**
     * get user info
     * @return \Illuminate\Http\JsonResponse
     * @author Samh Dev
     */
    public function index()
    {
        $data = $this->countryRepository->all(false);
        if (!$data) return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
        return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
    }
    
    /**
     * get user info
     * @return \Illuminate\Http\JsonResponse
     * @author Samh Dev
     */
    public function cities()
    {
        $data = $this->cityRepository->allAsQuery()->get(['id','name','country_id']);
        if (!$data) return JsonResponse::respondError(JsonResponse::MSG_BAD_REQUEST);
        return JsonResponse::respondSuccess(JsonResponse::MSG_SUCCESS, $data);
    }

}

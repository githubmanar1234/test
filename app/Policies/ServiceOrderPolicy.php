<?php

namespace App\Policies;

use App\Helpers\Constants;
use App\Models\Service;
use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class ServiceOrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Service $service
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Service $service)
    {
        //
    }


    /**
     * Determine whether the user can create models.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        //
    }

    /**
     * @param User $user
     * @param ServiceOrder $serviceOrder
     * @return bool
     */
    public function acceptServiceOrder(User $user, ServiceOrder $serviceOrder)
    {

        return ($serviceOrder->service->user_id == $user->id && $serviceOrder->status == Constants::STATUS_UNDER_REVIEW);
    }

    /**
     * @param User $user
     * @param ServiceOrder $serviceOrder
     * @return bool
     */
    public function rejectServiceOrder(User $user, ServiceOrder $serviceOrder)
    {
        return ($serviceOrder->service->user_id == $user->id && $serviceOrder->status == Constants::STATUS_UNDER_REVIEW);
    }

    /**
     * @param User $user
     * @param ServiceOrder $serviceOrder
     * @return bool
     */
    public function completeServiceOrder(User $user, ServiceOrder $serviceOrder)
    {
        return ($serviceOrder->service->user_id == $user->id && $serviceOrder->status == Constants::ORDER_STATUS_UNDERWAY);
    }
    /**
     * rate service order police
     * @param User $user
     * @param ServiceOrder $serviceOrder
     * @return bool
     */
    public function rateServiceOrder(User $user, ServiceOrder $serviceOrder)
    {
        return ($serviceOrder->user_id == $user->id && $serviceOrder->status == Constants::ORDER_STATUS_COMPLETED);
    }

}

<?php

namespace App\Policies;

use App\Helpers\Constants;
use App\Models\Service;
use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class ServicePolicy
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
        return !isset($service->parent_id) && (($service->status == Constants::STATUS_ACCEPTED) || $service->user->id == $user->id);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Service $service
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function getService(User $user, Service $service)
    {
        if ($user->id != $service->user()->first()->id)
            return $service->status == Constants::STATUS_ACCEPTED;
        return true;
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
     * Determine whether the user can update the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Service $service
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function updateService(User $user, Service $service)
    {
        return !isset($service->parent_id) && !($service->serviceUpdate()->first()) && $service->user->id == $user->id && ($service->status == Constants::STATUS_REJECTED || $service->status == Constants::STATUS_ACCEPTED);
    }

    /**
     * @param User $user
     * @param Service $service
     * @return bool
     * Determine whether the admin can accept ServiceUpdate.
     */
    public function acceptServiceUpdate(User $user, Service $service)
    {
        return isset($service->parent_id) && $service->status == Constants::STATUS_UNDER_REVIEW;
    }


    /**
     * Determine whether the user can report the service.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Service $service
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function reportService(User $user, Service $service)
    {
        return !isset($service->parent_id) && ($service->user->id !== $user->id && $service->status == Constants::STATUS_ACCEPTED);
    }

    /**
     * Determine whether the user can report the service.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Service $service
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function subscribeToService(User $user, Service $service)
    {

        return !isset($service->parent_id) && ($service->user->id !== $user->id && $service->status == Constants::STATUS_ACCEPTED);
    }

//    /**
//     * @param User $user
//     * @param ServiceOrder $serviceOrder
//     * @return bool
//     */
//    public function acceptServiceOrder(User $user, ServiceOrder $serviceOrder)
//    {
//        return ($serviceOrder->order->user_id == $user->id && $serviceOrder->status == Constants::STATUS_UNDER_REVIEW);
//    }
//
//    /**
//     * @param User $user
//     * @param ServiceOrder $serviceOrder
//     * @return bool
//     */
//    public function rejectServiceOrder(User $user, ServiceOrder $serviceOrder)
//    {
//        return ($serviceOrder->order->user_id == $user->id && $serviceOrder->status == Constants::ORDER_STATUS_UNDER_REVIEW);
//    }
//
//    /**
//     * @param User $user
//     * @param ServiceOrder $serviceOrder
//     * @return bool
//     */
//    public function completeServiceOrder(User $user, ServiceOrder $serviceOrder)
//    {
//        return ($serviceOrder->user_id == $user->id && $serviceOrder->status == Constants::ORDER_STATUS_UNDERWAY);
//    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Service $service
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Service $service)
    {
        return $service->user->id == $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Service $service
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Service $service)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Service $service
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Service $service)
    {
        //
    }
}

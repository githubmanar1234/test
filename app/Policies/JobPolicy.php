<?php

namespace App\Policies;

use App\Helpers\Constants;
use App\Models\Job;
use App\Models\Service;
use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class JobPolicy
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
     * @param \App\Models\Job $job
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Job $job)
    {
        return !isset($job->parent_id) && (($job->status == Constants::STATUS_ACCEPTED) || $job->user->id == $user->id);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Job $job
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function getJob(User $user, Job $job)
    {
        if ($user->id != $job->user()->first()->id)
            return $job->status == Constants::STATUS_ACCEPTED;
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
     * @param \App\Models\Job $job
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function updateJob(User $user, Job $job)
    {
        return !isset($job->parent_id) && !($job->jobUpdate()->first()) && $job->user->id == $user->id && ($job->status == Constants::STATUS_REJECTED || $job->status == Constants::STATUS_ACCEPTED);
    }

    /**
     * Determine whether the user can report the service.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Job $job
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function reportJob(User $user, Job $job)
    {

        return ($job->user->id !== $user->id && $job->status == Constants::STATUS_ACCEPTED);
    }



    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Service $service
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Job $job)
    {
        return $job->user->id == $user->id ;
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

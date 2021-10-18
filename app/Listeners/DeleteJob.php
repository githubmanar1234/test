<?php

namespace App\Listeners;

use App\Events\JobReported;
use App\Events\ServiceReported;
use App\Helpers\Constants;
use App\Jobs\Messages\SendSinglePushNotification;
use App\Models\Job;
use App\Models\Report;
use App\Models\Service;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Queue\InteractsWithQueue;

class DeleteJob implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param ServiceReported $event
     * @return void
     */
    public function handle(JobReported $event)
    {
        $event->job->delete();
        SendSinglePushNotification::dispatch(trans('notifications.notifications_title'), trans('notifications.service_has_been_deleted'), $event->service->user->fcm_token, [Constants::NOTIFICATION_TYPE_KEY => Constants::NOTIFICATION_TYPE_JOB]);

    }

    public function shouldQueue(JobReported $event)
    {
        $reportsCount = Report::where('reportable_id', $event->job->id)->where('reportable_type', Job::class)->count();

        return $reportsCount >= Constants::NUMBER_OF_REPORTS_TO_DELETE_JOB;
    }

}

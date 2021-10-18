<?php

namespace App\Listeners;

use App\Events\ServiceReported;
use App\Helpers\Constants;
use App\Jobs\Messages\SendSinglePushNotification;
use App\Models\Report;
use App\Models\Service;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Queue\InteractsWithQueue;

class DeleteService implements ShouldQueue
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
    public function handle(ServiceReported $event)
    {
        $event->service->delete();
        SendSinglePushNotification::dispatch(trans('notifications.notifications_title'), trans('notifications.service_has_been_deleted'), $event->service->user->fcm_token, [Constants::NOTIFICATION_TYPE_KEY => Constants::NOTIFICATION_TYPE_SERVICE]);

    }

    public function shouldQueue(ServiceReported $event)
    {
        $reportsCount = Report::where('reportable_id', $event->service->id)->where('reportable_type', Service::class)->count();

        return $reportsCount >= Constants::NUMBER_OF_REPORTS_TO_DELETE_SERVICE;
    }

}

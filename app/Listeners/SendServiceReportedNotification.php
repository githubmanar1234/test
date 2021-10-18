<?php

namespace App\Listeners;

use App\Events\ServiceReported;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendServiceReportedNotification implements ShouldQueue
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
     * @param  ServiceReported  $event
     * @return void
     */
    public function handle(ServiceReported $event)
    {
        //
    }
}

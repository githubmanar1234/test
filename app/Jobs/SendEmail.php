<?php

namespace App\Jobs;

use App\Mail\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected  $mailable;
    protected  $mailTo;
    /**
     * SendEmail constructor.
     * @param ResetPassword $resetPassword
     */
    public function __construct(Mailable $mailable,$mailTo)
    {

        $this->mailable = $mailable;
        $this->mailTo = $mailTo;
    }


    public function handle()
    {
        Mail::to($this->mailTo)->send($this->mailable);
    }
}

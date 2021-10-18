<?php

namespace App\Jobs\Messages;

use App\Helpers\FcmNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;


class SendSinglePushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $title;
    private $body;
    private $token;
    private $data;

    /**
     * SendSinglePushNotification constructor.
     * @param $title
     * @param $body
     * @param $token
     * @param $data
     */
    public function __construct($title, $body, $token,$data)
    {
        $this->title = $title;
        $this->body = $body;
        $this->token = $token;
        $this->data = $data;
    }

    /**
     * @throws \Kreait\Firebase\Exception\FirebaseException
     * @throws \Kreait\Firebase\Exception\MessagingException
     */
    public function handle()
    {
        try{
            Log::info($this->token);
            (new FcmNotification($this->title, $this->body,$this->data))->send($this->token);

        } catch (\Exception $exception){
            Log::error($exception->getMessage());
        }
    }
}

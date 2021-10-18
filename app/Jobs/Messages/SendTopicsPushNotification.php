<?php

namespace App\Jobs\Messages;

use App\Helpers\FcmNotification;
use App\Http\Repositories\IRepositories\ITicketRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;


class SendTopicsPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $title;
    private $body;
    private $topic;
    private $data;

    /**
     * SendTopicsPushNotification constructor.
     * @param $title
     * @param $body
     * @param $topic
     * @param $data
     */
    public function __construct($title, $body, $topic,$data)
    {
        $this->title = $title;
        $this->body = $body;
        $this->topic = $topic;
        $this->data = $data;
    }

    /**
     * @throws \Kreait\Firebase\Exception\FirebaseException
     * @throws \Kreait\Firebase\Exception\MessagingException
     */
    public function handle()
    {

        if (is_array($this->topic))
            foreach ($this->topic as $topic) {
                (new FcmNotification($this->title, $this->body))->sendToTopic($topic);
            }
        else (new FcmNotification($this->title, $this->body,$this->data))->sendToTopic($this->topic);
    }
}

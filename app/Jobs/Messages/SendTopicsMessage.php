<?php

namespace App\Jobs\Messages;

use App\Helpers\Constants;
use App\Helpers\FcmNotification;
use App\Http\Repositories\IRepositories\IMessageRepository;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;


class SendTopicsMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $message;
    private $topic;

    /**
     * SendTopicsMessage constructor.
     * @param $message
     * @param $topic
     */
    public function __construct($message, $topic)
    {
        $this->message = $message;
        $this->topic = $topic;
    }

    /**
     * @param IMessageRepository $messageRepository
     * @throws \Kreait\Firebase\Exception\FirebaseException
     * @throws \Kreait\Firebase\Exception\MessagingException
     */
    public function handle(IMessageRepository $messageRepository)
    {
        if (is_array($this->topic))
            foreach ($this->topic as $topic) {
                (new FcmNotification($this->message->title, $this->message->body, ['title' => $this->message->title, 'message' => $this->message->body, Constants::NOTIFICATION_TYPE_KEY => Constants::NOTIFICATION_TYPE_HOME]))->sendToTopic($topic);
            }
        else  (new FcmNotification($this->message->title, $this->message->body, ['title' => $this->message->title, 'message' => $this->message->body, Constants::NOTIFICATION_TYPE_KEY => Constants::NOTIFICATION_TYPE_HOME]))->sendToTopic($this->topic);
    }
}

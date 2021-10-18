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


class SendMulticastPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $title;
    private $body;
    private $tokens;

    /**
     * generateTicket constructor.
     * @param ITicketRepository $ticketRepository
     * @param $userId
     * @param $pollingId
     */
    public function __construct($title, $body, $tokens)
    {
        $this->title = $title;
        $this->body = $body;
        $this->tokens = $tokens;
    }

    /**
     * @throws \Kreait\Firebase\Exception\FirebaseException
     * @throws \Kreait\Firebase\Exception\MessagingException
     */
    public function handle()
    {
        (new FcmNotification($this->title, $this->body))->sendMulticast($this->tokens);
    }
}

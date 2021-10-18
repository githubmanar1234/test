<?php


namespace App\Helpers;


use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\WebPushConfig;

class FcmNotification
{
    private $message;
    private $messaging;
    private $androidConfig;
    private $notification;
    private $apnConfig;
    private $pushConfig;
    private $token;
    private $data = array();

    /**
     * FcmNotification constructor.
     * @param $title
     * @param $body
     * @param null $data
     */
    public function __construct($title, $body, $data = null)
    {
        $this->data = $data;
        $this->messaging = (new Factory)->withServiceAccount(base_path(env("FIREBASE_CREDENTIALS")))->createMessaging();
        $this->notification = Notification::fromArray([
            'title' => $title,
            'body' => $body,
//            'image' => "",
        ]);
        $this->androidConfig = AndroidConfig::fromArray([
            'ttl' => '3600s',
            'priority' => 'normal',
            'notification' => [
                'title' => $title,
                'body' => $body,
                'icon' => 'stock_ticker_update',
                'color' => '#f45342',
                'sound' => 'default',
            ],
        ]);
        $this->apnConfig = ApnsConfig::fromArray([
            'headers' => [
                'apns-priority' => '10',
            ],
            'payload' => [
                'aps' => [
                    'alert' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'badge' => 42,
                    'sound' => 'default',
                ],
            ],
        ]);
        $this->pushConfig = WebPushConfig::fromArray([
            'notification' => [
                'title' => $title,
                'body' => $body,
                'icon' => 'https://my-server/icon.png',
            ],
            'fcm_options' => [
                'link' => 'https://my-server/some-page',
            ],
        ]);

    }

    /**
     * @throws \Kreait\Firebase\Exception\FirebaseException
     * @throws \Kreait\Firebase\Exception\MessagingException
     */
    public function send($token)
    {
        if (isset($this->data))
            $this->message = CloudMessage::withTarget('token', $token)
                ->withNotification($this->notification)
                ->withDefaultSounds()// Enables default notifications sounds on iOS and Android devices.
                ->withApnsConfig($this->apnConfig)
                ->withAndroidConfig($this->androidConfig)
                ->withWebPushConfig($this->pushConfig)->withData($this->data);
        else
            $this->message = CloudMessage::withTarget('token', $token)
                ->withNotification($this->notification)
                ->withDefaultSounds()// Enables default notifications sounds on iOS and Android devices.
                ->withApnsConfig($this->apnConfig)
                ->withAndroidConfig($this->androidConfig)
                ->withWebPushConfig($this->pushConfig);
        $this->messaging->send($this->message);
    }

    /**
     * @param $topic
     * @throws \Kreait\Firebase\Exception\FirebaseException
     * @throws \Kreait\Firebase\Exception\MessagingException
     */
    public function sendToTopic($topic)
    {

        if (isset($this->data))
            $this->message = CloudMessage::withTarget('topic', $topic)
                ->withNotification(['title' => 'Notification title', 'body' => 'Notification body'])
                ->withDefaultSounds()// Enables default notifications sounds on iOS and Android devices.
                ->withApnsConfig($this->apnConfig)
                ->withAndroidConfig($this->androidConfig)
                ->withWebPushConfig($this->pushConfig)->withData($this->data);
        else
            $this->message = CloudMessage::withTarget('topic', $topic)
                ->withNotification(['title' => 'Notification title', 'body' => 'Notification body'])
                ->withDefaultSounds()// Enables default notifications sounds on iOS and Android devices.
                ->withApnsConfig($this->apnConfig)
                ->withAndroidConfig($this->androidConfig)
                ->withWebPushConfig($this->pushConfig);
        $this->messaging->send($this->message);
    }

    /**
     * @throws \Kreait\Firebase\Exception\FirebaseException
     * @throws \Kreait\Firebase\Exception\MessagingException
     */
    public function sendMulticast($tokens)
    {
        if (isset($this->data))
            $this->message = CloudMessage::new()
                ->withNotification(['title' => 'Notification title', 'body' => 'Notification body'])
                ->withDefaultSounds()// Enables default notifications sounds on iOS and Android devices.
                ->withApnsConfig($this->apnConfig)
                ->withAndroidConfig($this->androidConfig)
                ->withWebPushConfig($this->pushConfig)
                ->withData($this->data);
        else
            $this->message = CloudMessage::new()
                ->withNotification(['title' => 'Notification title', 'body' => 'Notification body'])
                ->withDefaultSounds()// Enables default notifications sounds on iOS and Android devices.
                ->withApnsConfig($this->apnConfig)
                ->withAndroidConfig($this->androidConfig)
                ->withWebPushConfig($this->pushConfig);

        $this->messaging->sendMulticast($this->message, $tokens);
    }
}
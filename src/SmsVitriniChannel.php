<?php

namespace HasnHasan\SmsVitrini;

use Illuminate\Notifications\Notification;
use HasnHasan\SmsVitrini\Exceptions\CouldNotSendNotification;
use GuzzleHttp\Client;

/**
 * Class SmsVitriniChannel.
 */
final class SmsVitriniChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed                                  $notifiable
     * @param  \Illuminate\Notifications\Notification $notification
     * @throws \HasnHasan\SmsVitrini\Exceptions\CouldNotSendNotification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toSmsVitrini($notifiable);
        $to = $notifiable->routeNotificationFor('SmsVitrini');

        if (empty($to)) {
            throw CouldNotSendNotification::missingRecipient();
        }
        
        $client = new Client();
        $guzzleResponse = $client->request('POST', $this->url, [
            'islem'     => 1
            'user'      => config('services.smsvitrini.username'),
            'pass'      => config('services.smsvitrini.password'),
            'baslik'    => config('services.smsvitrini.title'), 
            'mesaj'     => $message,
            'numaralar' => $to
        ]);
        echo "<pre>";
        print_r ($guzzleResponse);
        echo "</pre>";
        echo "<pre>";
        print_r ($guzzleResponse->getBody()->getContents());
        echo "</pre>";
        die;
    }
}

<?php

namespace App\Listeners\PaymentUpdate;

use Sumra\SDK\Facades\PubSub;

/**
 * Class SendNotificationListener
 *
 * @package App\Listeners
 */
class SendNotificationListener
{
    /**
     * @param $document
     */
    public static function exec($document){
        // Send message to notificationMS
        PubSub::publish('SendNotificationListener', [
            'type' => class_basename(get_class($document)),
            'user_id' => $document->user_id,
            'service' => env('RABBITMQ_EXCHANGE_NAME'),
            'message' => $document,
        ], config('pubsub.queue.notifications'));
    }
}

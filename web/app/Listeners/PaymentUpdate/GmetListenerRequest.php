<?php

namespace App\Listeners\PaymentUpdate;

use Sumra\SDK\Facades\PubSub;

/**
 * Class GmetListenerRequest
 *
 * @package App\Listeners
 */
class GmetListenerRequest
{
    /**
     * @param $document
     */
    public static function exec($document){
        // Send message to notificationMS
        PubSub::publish('GmetListenerRequest', [
            'type' => class_basename(get_class($document)),
            'user_id' => $document->user_id,
            'service' => env('RABBITMQ_EXCHANGE_NAME'),
            'message' => $document,
        ], config('pubsub.queue.notifications'));
    }
}

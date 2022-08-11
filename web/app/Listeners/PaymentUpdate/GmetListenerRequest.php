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
        $message = [
            'amount' => $document->amount,
            'amount_token' => $document->total_token,
            'document_id' => $document->id,
            'currency' => $document->currency_code,
            'document_object' => class_basename(get_class($document)),
        ];
        PubSub::publish('GmetListenerRequest', [
            'type' => class_basename(get_class($document)),
            'user_id' => $document->user_id,
            'service' => env('APP_PLATFORM'),
            'message' => $message,
        ], config('pubsub.queue.notifications'));
    }
}

<?php

namespace App\Services;

use App\Models\Contributor;
use App\Models\Identification;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\RequestInterface;

class IdentityVerification
{
    /**
     * @var Client
     */
    protected Client $client;

    protected $document_types = [
        1 => 'PASSPORT',
        2 => 'ID_CARD',
        3 => 'DRIVERS_LICENSE',
        4 => 'RESIDENCE_PERMIT'
    ];

   // const STATUS_

    /**
     * IdentityVerification constructor.
     */
    public function __construct()
    {
        // Setup client configuration
        $clientConfig = [
            'base_uri' => config('identity.veriff.base_url'),
            'headers' => [
                'Content-Type' => 'application/json',
                'x-auth-client' => config('identity.veriff.public_key')
            ],
            'timeout' => 40
        ];

        // Set debug info
        if (env('APP_DEBUG', 0)) {
            $stack = HandlerStack::create();
            $stack->push(Middleware::mapRequest(function (RequestInterface $request) {
                Log::info("### Veriff KYC Request:");
                Log::info("URI: {$request->getUri()}");
                Log::info('Headers: ', $request->getHeaders());
                Log::info('Request: ', json_decode($request->getBody(), true));

                return $request;
            }));

            $clientConfig['handler'] = $stack;
        }

        // Init HTTP Client
        $this->client = new Client($clientConfig);
    }

    /**
     * @param Contributor $contributor
     * @return mixed|object
     */
    public function startSession(Contributor $contributor, Request $request): mixed // Request $request
    {
        $body = [
            'json' => [
                'verification' => [
                    //'callback' => 'https://localhost:18105',
                    'person' => [
                        'firstName' => $contributor->first_name,
                        'lastName' => $contributor->last_name,
                        //'idNumber' => ''
                    ],
                    'document' => [
                        //'number' => '',
                        'type' => $this->document_types[$request->get('document_type')],// 'ID_CARD',
                        'country' => $contributor->address_country
                    ],
                    'vendorData' => json_encode([
                        'user_id' => $contributor->id
                    ]),
                    'timestamp' => Carbon::now()
                ]
            ]
        ];

        try {
            $response = $this->client->request('POST', '/v1/sessions/', $body);
            $data = json_decode($response->getBody());

            return (object)[
                'status' => $data->status,
                'verification' => [
                    'sessionUrl' => $data->verification->url,
                    'sessionToken' => $data->verification->sessionToken
                ]
            ];
        } catch (ClientException $e) {
            return json_decode($e->getResponse()->getBody());
        }
    }

    /**
     * @param Request $request
     */
    public function handleWebhook($type, Request $request)
    {
        // Get request headers
        $headers = $request->headers->all();

        dd($headers);

        if($headers->get('x-auth-client') !== config('identity.veriff.public_key')){
            return response('Incorrect Public Key', 401);
        }


        // Get request data
        $payload = $request->all();

        Log::info("Type: {$type}");
        Log::info("Headers: ", $headers);
        Log::info("Request: ", $payload);


        $signature = strtolower(hash_hmac('sha256', json_encode($payload), config('identity.veriff.private_key')));

        Log::info("Signature: {$signature}");

//
//        $vendorData = json_decode($request['vendorData']);
//
//        Identification::create([
//            'session_id' => $payload['id'],
//            'contributor_id' => $vendorData->user_id,
//            'status' => $payload['code']
//        ]);
    }
}

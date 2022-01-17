<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Psr\Http\Message\RequestInterface;

class IdentityVerification
{
    /**
     * @var Client
     */
    protected Client $client;

    /**
     * IdentityVerification constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $stack = HandlerStack::create();

        // my middleware
        $stack->push(Middleware::mapRequest(function (RequestInterface $request) {
//            $contentsRequest = (string) $request->getBody();
//            dd($contentsRequest);

            return $request;
        }));

        $this->client = new Client([
            'base_uri' => config('identity.veriff.base_url'),
            'handler' => $stack,
            'headers' => [
                'Content-Type' => 'application/json',
                'X-AUTH-CLIENT' => config('identity.veriff.public_key')
            ],
            'timeout' => 40
        ]);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function verify()
    {
        $body = [
            "verification" => [
                "callback" => "https://veriff.com",
                "person" => [
                    "firstName" => "John",
                    "lastName" => "Smith",
                    "idNumber" => "123456789"
                ],
                "document" => [
                    "number" => "B01234567",
                    "type" => "PASSPORT",
                    "country" => "EE"
                ],
                "vendorData" => "11111111",
                "timestamp" => "2016-05-19T08:30:25.597Z"
            ]
        ];

        $this->guzzleParams['json'] = $body;


        $response = $this->client->request(
            'POST',
            '/session/',
            $this->guzzleParams
        );

        dd(
            $response,
//                json_decode($response->getBody())
        );

        try {
            $response = $this->client->request(
                'POST',
                '/session/',
                $this->guzzleParams
            );

            dd(
            $response,
//                json_decode($response->getBody())
            );


        } catch (ClientException $e) {
            dd($e);
        }
    }
}

<?php

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class ElasticService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => config('elasticsearch.host'),
        ]);
    }

    /**
     * Send an HTTP request to Elasticsearch.
     *
     * @param string $method
     * @param string $uri
     * @param mixed|null $body
     * @param array $headers
     * @return array
     * @throws GuzzleException
     */
    public function sendRequest(string $method, string $uri, mixed $body = null, array $headers = ['Content-Type' => 'application/json']): array
    {
        try {
            $response = $this->client->request($method, $uri, [
                'body' => $body,
                'headers' => $headers,
            ]);

            return [
                'status' => $response->getStatusCode(),
                'data' => json_decode($response->getBody(), true),
            ];
        } catch (\Exception $e) {
            return [
                'status' => $e->getCode(),
                'error' => $e->getMessage(),
            ];
        }
    }
}

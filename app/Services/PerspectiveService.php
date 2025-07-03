<?php

namespace App\Services;

use GuzzleHttp\Client;

class PerspectiveService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = env('PERSPECTIVE_API_KEY'); // Store your API key in .env
    }

    public function analyzeText($text)
    {
        $url = "https://commentanalyzer.googleapis.com/v1alpha1/comments:analyze?key={$this->apiKey}";

        $response = $this->client->post($url, [
            'json' => [
                'comment' => ['text' => $text],
                'languages' => ['en'],
                'requestedAttributes' => [
                    'SEVERE_TOXICITY' => new \stdClass(),
                    'IDENTITY_ATTACK' => new \stdClass(),
                    'THREAT' => new \stdClass()
                ],
            ]
        ]);

        $body = json_decode($response->getBody(), true);
        // return $body['attributeScores']['TOXICITY']['summaryScore']['value'] ?? null;
        $attributes = ['SEVERE_TOXICITY', 'IDENTITY_ATTACK', 'THREAT'];

        $scores = [];

        foreach ($attributes as $attribute) {
            $scores[$attribute] = $body['attributeScores'][$attribute]['summaryScore']['value'] ?? null;
        }

        return $scores;
    }
}

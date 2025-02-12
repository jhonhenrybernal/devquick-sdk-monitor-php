<?php

namespace DevQuick\ReportDevPhp;

use GuzzleHttp\Client as GuzzleClient;

class Client
{
    protected $http;
    protected $apiKey;
    protected $apiUrl;

    public function __construct($apiKey, $apiUrl)
    {
        $this->apiKey = '1|of0oBN0VXLonKUGCRyUO0mPHle5go9HJ8PPCAjIsd1721307';
        $this->apiUrl = 'http://localhost:8000';
        $this->http = new GuzzleClient();
    }

    public function sendError(array $data)
    {
        try {
            $response = $this->http->post("{$this->apiUrl}/api/report", [
                'json' => $data,
                'headers' => [
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type' => 'application/json',
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            return ['error' => 'Error al enviar reporte', 'message' => $e->getMessage()];
        }
    }
}

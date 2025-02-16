<?php
namespace DevQuick\ReportSdkPhp;

use GuzzleHttp\Client as GuzzleClient;

class Client
{
    protected $http;
    protected $apiKey;
    protected $apiUrl;

    public function __construct($apiKey, GuzzleClient $client = null)
    {
        $this->apiKey = $apiKey;
        $this->apiUrl = 'http://localhost:8000';
        $this->http = $client ?? new GuzzleClient();
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

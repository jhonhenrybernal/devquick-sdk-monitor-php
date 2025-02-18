<?php
namespace DevQuick\ReportSdkPhp;

use GuzzleHttp\Client as GuzzleClient;

class Client
{
    protected $http;
    protected $apiKey;
    protected $apiUrl;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
        $this->apiUrl = 'http://localhost:8000'; // Cambia por la URL real del backend
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

            $body = json_decode($response->getBody()->getContents(), true);
            return is_array($body) ? $body : ['error' => 'Respuesta no válida del servidor'];
        } catch (\Exception $e) {
            return ['error' => 'Error al enviar reporte', 'message' => $e->getMessage()];
        }
    }
}
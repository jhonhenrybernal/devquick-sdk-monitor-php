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
            // Registrar los datos antes de enviarlos
            file_put_contents(__DIR__ . '/sdk_debug.log', "📡 Enviando datos al backend:\n" . json_encode($data, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);

            $response = $this->http->post("{$this->apiUrl}/api/report", [
                'json' => $data,
                'headers' => [
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ]
            ]);

            $responseBody = $response->getBody()->getContents();

            // Registrar la respuesta del backend
            // file_put_contents(__DIR__ . '/sdk_debug.log', "✅ Respuesta del backend:\n$responseBody\n", FILE_APPEND);

            return json_decode($responseBody, true);
        } catch (\Exception $e) {
            // Registrar cualquier error que ocurra en la comunicación con el backend
            file_put_contents(__DIR__ . '/sdk_debug.log', "❌ Error al enviar reporte: " . $e->getMessage() . "\n", FILE_APPEND);
            return ['error' => 'Error al enviar reporte', 'message' => $e->getMessage()];
        }
    }

}
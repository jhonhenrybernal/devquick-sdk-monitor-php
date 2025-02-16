<?php

namespace DevQuick\ReportSdkPhp;

use DevQuick\ReportSdkPhp\Client;
use Carbon\Carbon;
use GuzzleHttp\Client as GuzzleClient;

class DevQuickMonitor
{
    protected $client;
    protected $httpClient;

    public function __construct($apiKey)
    {
        $this->client = new Client($apiKey);
        $this->httpClient = new GuzzleClient(); // Cliente HTTP por defecto

        // Capturar errores fatales
        register_shutdown_function([$this, 'handleFatalError']);

        // Capturar excepciones no manejadas
        set_exception_handler([$this, 'reportException']);
    }

    public function setClient()
    {
        $this->httpClient = $this->client;
    }

    public function reportException(\Throwable $exception)
    {
        return $this->client->sendError([
            'error_type' => get_class($exception),
            'error_message' => $exception->getMessage(),
            'stack_trace' => $exception->getTraceAsString(),
            'timestamp' => Carbon::now()->toDateTimeString(),
            'sdk_version' => '1.0.0',
        ]);
    }

    public function handleFatalError()
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $exception = new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
            $this->reportException($exception);
        }
    }
}

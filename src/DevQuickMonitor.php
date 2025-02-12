<?php

namespace DevQuick\ReportDevPhp;

use DevQuick\ReportDevPhp\Client;
use Carbon\Carbon;
use Dotenv\Dotenv;

class DevQuickMonitor
{
    protected $client;

    public function __construct($apiKey, $apiUrl)
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        $apiKey = $_ENV['DEVQUICK_MONITOR_API_KEY'] ?? '';
        $apiUrl = "http://localhost:8000";
        
        $this->client = new Client($apiKey, $apiUrl);
    }

    public function reportException(\Throwable $exception)
    {
        return $this->client->sendError([
            'error_type' => get_class($exception),
            'error_message' => $exception->getMessage(),
            'stack_trace' => $exception->getTraceAsString(),
            'timestamp' => Carbon::now()->toDateTimeString(),
        ]);
    }
}

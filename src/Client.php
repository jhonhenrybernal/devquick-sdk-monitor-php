<?php

namespace DevQuick\ReportSdkPhp;

use DevQuick\ReportSdkPhp\Client;
use Carbon\Carbon;
use Dotenv\Dotenv;

class DevQuickMonitor
{
    protected $client;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        $apiKey = '1|of0oBN0VXLonKUGCRyUO0mPHle5go9HJ8PPCAjIsd1721307';
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
            'project_key' => '12213',
            'sdk_version' => '1.0.0',
        ]);
    }
}

<?php

namespace DevQuick\ReportSdkPhp;

use DevQuick\ReportSdkPhp\Client;
use Carbon\Carbon;
use GuzzleHttp\Client as GuzzleClient;

class DevQuickMonitor
{
    protected $client;

    public function __construct($apiKey)
    {
        // Se pasa solo `apiKey`
        $this->client = new Client($apiKey);
    }

    public function setClient(GuzzleClient $client)
    {
        $this->client->setHttpClient($client);
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
}

<?php

namespace DevQuick\ReportSdkPhp;

use DevQuick\ReportSdkPhp\Client;
use Carbon\Carbon;

class DevQuickMonitor
{
    protected $client;
    protected $apiKey;
    protected $apiUrl;
    protected $secretKey;

    public function __construct($apiKey, $encryptedApiUrl, $secretKey)
    {
        $this->apiKey = $apiKey;
        $this->secretKey = $secretKey;
        $this->apiUrl = $this->decryptUrl($encryptedApiUrl, $this->secretKey);

        $this->client = new Client($this->apiKey, $this->apiUrl);
    }

    private function decryptUrl($encryptedUrl, $key)
    {
        $data = base64_decode($encryptedUrl);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }

    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
        $this->client = new Client($this->apiKey, $this->apiUrl);
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

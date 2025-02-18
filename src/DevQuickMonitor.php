<?php

namespace DevQuick\ReportSdkPhp;

use DevQuick\ReportSdkPhp\Client;
use Carbon\Carbon;

class DevQuickMonitor
{
    protected $client;

    public function __construct($apiKey)
    {
        $this->client = new Client($apiKey);

        // Capturar errores fatales
        register_shutdown_function([$this, 'handleFatalError']);

        // Capturar excepciones no manejadas
        set_exception_handler([$this, 'reportException']);
    }

    public function reportException(\Throwable $exception)
    {
        
        $this->logSystemInfo([
            'error_type' => get_class($exception),
            'error_message' => $exception->getMessage(),
            'stack_trace' => $exception->getTraceAsString(),
            'timestamp' => Carbon::now()->toDateTimeString(),
            'sdk_version' => 'PHP 1.0.0',
            'system_info' => json_encode($this->getSystemInfo()),
            'security_scan' => json_encode($this->scanVulnerabilities())
        ]); // Guarda los datos antes de enviar
        return $this->client->sendError([
            'error_type' => get_class($exception),
            'error_message' => $exception->getMessage(),
            'stack_trace' => $exception->getTraceAsString(),
            'timestamp' => Carbon::now()->toDateTimeString(),
            'sdk_version' => 'PHP 1.0.0',
            'system_info' => json_encode($this->getSystemInfo()),
            'security_scan' => json_encode($this->scanVulnerabilities())
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

    private function getSystemInfo()
    {
        try {
            return [
                'os' => php_uname(),
                'language' => 'php',
                'php_version' => PHP_VERSION,
                'memory_usage' => memory_get_usage(),
                'memory_peak_usage' => memory_get_peak_usage(),
                'cpu_load' => $this->getCpuLoad(),
                'cpu_info' => $this->getCpuInfo(),
                'disk_free' => function_exists('disk_free_space') ? disk_free_space("/") : null,
                'disk_total' => function_exists('disk_total_space') ? disk_total_space("/") : null,
                'server_ip' => isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : getHostByName(getHostName()), // IP del servidor
                'client_ip' => $_SERVER['REMOTE_ADDR'] ?? 'N/A', // IP del cliente
                'loaded_extensions' => get_loaded_extensions(),
            ];
        } catch (\Exception $e) {
            return ['error' => 'Error al obtener la información del sistema: ' . $e->getMessage()];
        }
    }

    private function getCpuLoad()
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return is_array($load) ? implode(', ', $load) : 'N/A';
        }

        // Método alternativo para Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $cmd = 'wmic cpu get LoadPercentage';
            @exec($cmd, $output);

            if (isset($output[1])) {
                return trim($output[1]) . '%'; // Devuelve el porcentaje de uso del CPU
            }
        }

        return 'N/A';
    }


    private function getCpuInfo()
    {
        $cpuInfo = [
            'model' => 'N/A',
            'cores' => 'N/A',
            'threads' => 'N/A',
            'speed' => 'N/A',
            'processes' => 'N/A'
        ];

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Ejecuta el comando y obtiene la salida
            @exec('wmic cpu get Name, NumberOfCores, NumberOfLogicalProcessors, MaxClockSpeed', $output);

            if (!empty($output) && count($output) > 1) {
                $data = array_values(array_filter(array_map('trim', explode("\n", implode("\n", $output)))));

                if (count($data) > 1) {
                    $headers = preg_split('/\s{2,}/', trim($data[0])); // Divide por múltiples espacios
                    $values = preg_split('/\s{2,}/', trim($data[1]));

                    if (count($headers) === count($values)) {
                        $cpuInfo = [
                            'model' => $values[array_search('Name', $headers)] ?? 'N/A',
                            'cores' => $values[array_search('NumberOfCores', $headers)] ?? 'N/A',
                            'threads' => $values[array_search('NumberOfLogicalProcessors', $headers)] ?? 'N/A',
                            'speed' => ($values[array_search('MaxClockSpeed', $headers)] ?? 'N/A') . ' MHz',
                            'processes' => trim(shell_exec('tasklist | find /C /V ""')) ?? 'N/A'
                        ];
                    }
                }
            }
        } elseif (file_exists('/proc/cpuinfo')) {
            // Método alternativo para Linux
            $cpuInfo['model'] = trim(shell_exec(escapeshellcmd("grep 'model name' /proc/cpuinfo | uniq | awk -F': ' '{print $2}'"))) ?: 'N/A';
            $cpuInfo['cores'] = trim(shell_exec(escapeshellcmd("nproc --all"))) ?: 'N/A';
            $cpuInfo['threads'] = trim(shell_exec(escapeshellcmd("grep -c processor /proc/cpuinfo"))) ?: 'N/A';
            $cpuInfo['speed'] = trim(shell_exec(escapeshellcmd("lscpu | grep 'CPU MHz' | awk '{print $3}'"))) . ' MHz' ?: 'N/A';
            $cpuInfo['processes'] = trim(shell_exec(escapeshellcmd("ps -e | wc -l"))) ?: 'N/A';
        }

        return $cpuInfo;
    }
    private function getPublicIP()
    {
        try {
            $response = @file_get_contents('https://api64.ipify.org?format=json');
            return $response ? json_decode($response, true)['ip'] : 'N/A';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }
    
    private function checkOpenPorts()
    {
        $ports = [21, 22, 25, 80, 443, 3306, 6379, 8080];
        $openPorts = [];
    
        foreach ($ports as $port) {
            if (@fsockopen('localhost', $port, $errno, $errstr, 0.5)) {
                $openPorts[] = $port;
            }
        }
    
        return empty($openPorts) ? '✅ No hay puertos expuestos' : '⚠️ Puertos abiertos: ' . implode(', ', $openPorts);
    }
    
    private function checkServerSoftware()
    {
        return $_SERVER['SERVER_SOFTWARE'] ?? 'N/A';
    }
    
    private function checkPhpVersion()
    {
        $version = PHP_VERSION;
        return version_compare($version, '8.1.0', '<') ? "⚠️ Obsoleta ($version)" : "✅ Actual ($version)";
    }
    
    private function checkPhpExposure()
    {
        return ini_get('expose_php') ? '⚠️ Activado (Inseguro)' : '✅ Desactivado (Seguro)';
    }
    
    private function checkPhpErrorsEnabled()
    {
        return ini_get('display_errors') == 1 ? '⚠️ Activado (Inseguro)' : '✅ Desactivado (Seguro)';
    }
    
    private function checkDisabledFunctions()
    {
        $disabled = ini_get('disable_functions');
        return empty($disabled) ? '⚠️ No hay funciones deshabilitadas (Inseguro)' : '✅ Algunas funciones están deshabilitadas';
    }
    
    private function checkFilePermissions()
    {
        $files = ['.env', 'config.php', 'database.php'];
        $insecureFiles = [];
    
        foreach ($files as $file) {
            if (file_exists(__DIR__ . '/' . $file) && is_writable(__DIR__ . '/' . $file)) {
                $insecureFiles[] = $file;
            }
        }
    
        return empty($insecureFiles) ? '✅ No hay archivos con permisos peligrosos' : '⚠️ Archivos inseguros: ' . implode(', ', $insecureFiles);
    }
    
    private function checkSuspiciousProcesses()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $processes = shell_exec('tasklist');
            return strpos($processes, 'cmd.exe') !== false ? '⚠️ CMD abierto (posible riesgo)' : '✅ No hay procesos sospechosos';
        } else {
            $processes = shell_exec('ps aux');
            return strpos($processes, 'nc') !== false ? '⚠️ Netcat detectado (posible ataque)' : '✅ No hay procesos sospechosos';
        }
    }
    
    public function scanVulnerabilities()
    {
        return [
            'php_errors' => $this->checkPhpErrorsEnabled(),
            'php_expose' => $this->checkPhpExposure(),
            'php_version' => $this->checkPhpVersion(),
            'server_software' => $this->checkServerSoftware(),
            'open_ports' => $this->checkOpenPorts(),
            'public_ip' => $this->getPublicIP(),
            'disabled_functions' => $this->checkDisabledFunctions(),
            'file_permissions' => $this->checkFilePermissions(),
            'suspicious_processes' => $this->checkSuspiciousProcesses(),
        ];
    }
    

    private function logSystemInfo($data)
    {
        file_put_contents(__DIR__ . '/sdk_debug.log', json_encode($data, JSON_PRETTY_PRINT), FILE_APPEND);
    }
}

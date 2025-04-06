<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class BotRunner
{
    public static function run($botId, $scriptPath, $logPath, $language = 'node')
    {
        $pidPath = storage_path("app/bot_pids/bot_{$botId}.pid");

        File::ensureDirectoryExists(dirname($logPath));
        File::ensureDirectoryExists(dirname($pidPath));

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Executa o bot em background e salva log (Windows)

            // Executa em segundo plano
            $cmd = "cmd /C start /B cmd /C \"{$language} {$scriptPath} > {$logPath} 2>&1\"";
            pclose(popen($cmd, "r"));

            // Aguarda processo iniciar e pega o último PID do Node
            sleep(1); // Tempo mínimo pro processo iniciar
            exec("wmic process where \"CommandLine like '%{$scriptPath}%' and name='{$language}.exe'\" get ProcessId /value", $output);

            $pidLine = collect($output)->first(fn($line) => str_contains($line, 'ProcessId='));
            $pid = trim(str_replace('ProcessId=', '', $pidLine ?? ''));

            if ($pid) {
                file_put_contents($pidPath, $pid);
            }

        } else {
            // Executa em segundo plano (Linux/macOS)
            $cmd = "{$language} \"$scriptPath\" > \"$logPath\" 2>&1 & echo $! > \"$pidPath\"";
            exec($cmd);
        }
    }

    public static function isRunning($botId)
    {
        $pidPath = storage_path("app/bot_pids/bot_{$botId}.pid");

        if (!file_exists($pidPath)) {
            return false;
        }

        $pid = trim(file_get_contents($pidPath));

        if (!$pid || !is_numeric($pid)) {
            return false;
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $output = [];
            exec("tasklist /FI \"PID eq $pid\"", $output);
            return collect($output)->contains(fn($line) => str_contains($line, (string)$pid));
        } else {
            return file_exists("/proc/$pid");
        }
    }

    public static function stop($botId)
    {
        $pidPath = storage_path("app/bot_pids/bot_{$botId}.pid");

        if (!file_exists($pidPath)) {
            return false;
        }

        $pid = trim(file_get_contents($pidPath));

        if (!$pid || !is_numeric($pid)) {
            return false;
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            exec("taskkill /F /PID $pid");
        } else {
            exec("kill -9 $pid");
        }

        unlink($pidPath);

        return true;
    }
}

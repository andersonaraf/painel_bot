<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class BotRunner
{
    public static function run($botId, $scriptPath, $logPath, $language = 'python')
    {
        $pidPath = storage_path("app/bot_pids/bot_{$botId}.pid");

        File::ensureDirectoryExists(dirname($logPath));
        File::ensureDirectoryExists(dirname($pidPath));

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Executa o bot em background no Windows

            // Executa o processo Python e redireciona o log
            $cmd = "cmd /C start /B cmd /C \"{$language} {$scriptPath} > {$logPath} 2>&1\"";
            pclose(popen($cmd, "r"));

            // Aguarda e captura o PID do processo com o caminho do script
            sleep(1);
            exec("wmic process where \"CommandLine like '%{$scriptPath}%' and name='python.exe'\" get ProcessId /value", $output);

            $pidLine = collect($output)->first(fn($line) => str_contains($line, 'ProcessId='));
            $pid = trim(str_replace('ProcessId=', '', $pidLine ?? ''));

            if ($pid) {
                file_put_contents($pidPath, $pid);
            }

        } else {
            // Executa no Linux/macOS
            $cmd = "python3 \"$scriptPath\" > \"$logPath\" 2>&1 & echo $! > \"$pidPath\"";
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

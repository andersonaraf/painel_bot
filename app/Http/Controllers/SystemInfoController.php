<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemInfoController extends Controller
{
    //
    public function index(): JsonResponse
    {
        $totalRamGb = $this->getTotalRamInGb();
        return response()->json([
            'system' => php_uname(),
            'cpu_arch' => php_uname('m'),
            'ram_total' => $totalRamGb,
            'ram_current' => round(memory_get_usage() / 1024 / 1024, 2),
            'ram_peak' => round(memory_get_peak_usage() / 1024 / 1024, 2),
            'disk_total' => round(disk_total_space('/') / 1024 / 1024 / 1024, 2),
            'disk_free' => round(disk_free_space('/') / 1024 / 1024 / 1024, 2),
        ]);
    }

    private function getTotalRamInGb(): float
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $output = shell_exec('wmic computersystem get TotalPhysicalMemory');
            preg_match_all('/\d+/', $output, $matches);
            $bytes = isset($matches[0][0]) ? (float) $matches[0][0] : 0;
        } else {
            $output = shell_exec('grep MemTotal /proc/meminfo');
            preg_match('/\d+/', $output, $matches);
            $kb = isset($matches[0]) ? (float) $matches[0] : 0;
            $bytes = $kb * 1024;
        }

        return round($bytes / 1024 / 1024 / 1024, 2);
    }
}

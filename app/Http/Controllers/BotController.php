<?php

namespace App\Http\Controllers;

use App\Models\Bot;
use App\Services\BotRunner;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\File;
class BotController extends Controller
{
    public function index()
    {
        $bots = Bot::all();
        return Inertia::render('bots/Index', [
            'bots' => $bots
        ]);
    }

    public function create()
    {
        return Inertia::render('bots/Edit', [
            'bot' => null
        ]);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nome' => 'required|string|max:255',
                'codigo' => 'required|string',
            ]);

            Bot::create([
                'nome' => $request->nome,
                'codigo' => $request->codigo,
            ]);

            return redirect()->route('bots.index'); // <- redireciona de volta para a lista
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }



    public function edit(Bot $bot)
    {
        return Inertia::render('bots/Edit', [
            'bot' => $bot
        ]);
    }


    public function update(Request $request, Bot $bot)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'codigo' => 'required|string',
        ]);

        $bot->update([
            'nome' => $request->nome,
            'codigo' => $request->codigo,
        ]);

        return redirect()->route('bots.index'); // <- redireciona também
    }


    public function destroy(Bot $bot)
    {
        $bot->delete();
        return redirect()->back();
    }


    public function execute(Bot $bot)
    {
        $scriptPath = storage_path("app/temp_bot_{$bot->id}.js");
        $logPath = storage_path("app/bot_logs/bot_{$bot->id}.log");

        File::put($scriptPath, $bot->codigo);

        BotRunner::run($bot->id, $scriptPath, $logPath, 'node');

        return response()->json(['success' => true]);
    }


    public function stop(Bot $bot)
    {
        return response()->json(['stopped' => BotRunner::stop($bot->id)]);
    }


    public function status(Bot $bot)
    {
        return response()->json(['running' => BotRunner::isRunning($bot->id)]);
    }

    public function log(Bot $bot)
    {
        $logPath = storage_path("app/bot_logs/bot_{$bot->id}.log");

        if (!file_exists($logPath)) {
            return response('', 204);
        }

        $size = filesize($logPath);
        $length = 4096; // últimos 4 KB

        $handle = fopen($logPath, "r");
        if ($size > $length) {
            fseek($handle, -$length, SEEK_END);
        }

        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }


}

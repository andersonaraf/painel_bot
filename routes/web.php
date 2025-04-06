<?php

use App\Http\Controllers\BotController;
use App\Http\Controllers\SystemInfoController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'verified']], function () {
    Route::get('system-info', [SystemInfoController::class, 'index'])->name('system-info');
    Route::resource('bots', BotController::class);
    Route::post('/bots/{bot}/execute', [BotController::class, 'execute'])->name('bots.execute');
    Route::get('/bots/{bot}/status', [BotController::class, 'status'])->name('bots.status');
    Route::get('/bots/{bot}/log', [BotController::class, 'log'])->name('bots.log');
    Route::post('/bots/{bot}/stop', [BotController::class, 'stop'])->name('bots.stop');

});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

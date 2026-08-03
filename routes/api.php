<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrinterController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/printer/save-job', [PrinterController::class, 'save_job'])->name('printer.save-job');
Route::post('/printer/get-jobs', [PrinterController::class, 'get_jobs'])->name('printer.get-jobs');
Route::post('/printer/delete-job', [PrinterController::class, 'delete_job'])->name('printer.delete-job');

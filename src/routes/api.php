<?php

use App\Http\Controllers\AttachmentsController;
use App\Http\Controllers\AutomationsController;
use App\Http\Controllers\EmailsController;
use App\Http\Controllers\FavoritesController;
use App\Http\Controllers\StatisticsController;
use Illuminate\Support\Facades\Route;

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

Route::middleware('mailcare.auth')->group(function () {
    Route::resource('emails', EmailsController::class);

    Route::post('emails/{email}/favorites', [FavoritesController::class, 'store']);
    Route::delete('emails/{email}/favorites', [FavoritesController::class, 'destroy']);

    Route::get('statistics', [StatisticsController::class, 'index']);

    Route::get('emails/{email}/attachments/{attachmentId}', [AttachmentsController::class, 'show']);

    Route::get('automations', [AutomationsController::class, 'index'])
        ->middleware('mailcare.config:automations');
    Route::post('automations', [AutomationsController::class, 'store'])
        ->middleware('mailcare.config:automations');
    Route::put('automations/{automation}', [AutomationsController::class, 'update'])
        ->middleware('mailcare.config:automations');
    Route::delete('automations/{automation}', [AutomationsController::class, 'destroy'])
        ->middleware('mailcare.config:automations');
});

<?php

use Illuminate\Http\Request;

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

    Route::resource('emails', 'EmailsController');

    Route::post('emails/{email}/favorites', 'FavoritesController@store');
    Route::delete('emails/{email}/favorites', 'FavoritesController@destroy');

    Route::get('statistics', 'StatisticsController@index');

    Route::get('emails/{email}/attachments/{attachmentId}', 'AttachmentsController@show');

    Route::get('automations', 'AutomationsController@index')
        ->middleware('mailcare.config:automations');
    Route::post('automations', 'AutomationsController@store')
        ->middleware('mailcare.config:automations');
    Route::put('automations/{automation}', 'AutomationsController@update')
        ->middleware('mailcare.config:automations');
    Route::delete('automations/{automation}', 'AutomationsController@destroy')
        ->middleware('mailcare.config:automations');
});

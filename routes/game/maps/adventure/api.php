<?php

// Map related info:
Route::get('/map/{user}', ['uses' => 'Api\MapController@index']);

// Map Movement:
Route::get('/is-water/{character}', ['uses' => 'Api\MapController@iswater']);
Route::post('/move/{character}', ['uses' => 'Api\MapController@move']);

// Set Sail:
Route::post('/map/set-sail/{location}/{character}', ['uses' => 'Api\MapController@setSail']);

Route::post('/map/teleport/{character}', ['uses' => 'Api\MapController@teleport']);

// Adventure:
Route::post('/character/{character}/adventure/{adventure}', ['uses' => 'Api\AdventureController@adventure']);
Route::post('/character/{character}/adventure/{adventure}/cancel', ['uses' => 'Api\AdventureController@cancelAdventure']);
Route::get('/character/adventure/logs', ['uses' => 'Api\AdventureController@getLogs']);

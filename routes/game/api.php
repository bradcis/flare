<?php

Route::get('/character-sheet/{character}', ['uses' => 'Api\CharacterSheetController@sheet']);

Route::get('/crafting/{character}', ['uses' => 'Api\CharacterSkillController@fetchItemsToCraft']);
Route::post('/craft/{character}', ['uses' => 'Api\CharacterSkillController@trainCrafting']);

Route::get('/character/{character}/adventure/{advnture}', ['uses' => 'Api\AdventureController@adventure']);

<?php

Route::group(['middleware' => 'web', 'prefix' => 'tags', 'namespace' => 'Modules\Tags\Http\Controllers'], function()
{
    Route::post('/tags/ajax', ['uses' => 'TagsController@ajax', 'laroute' => true])->name('tags.ajax');
});

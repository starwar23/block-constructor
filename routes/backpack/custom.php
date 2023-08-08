<?php

use App\Models\TurboPageContent;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => [
        'web',
        'admin'
    ],
    'namespace'  => 'App\Http\Controllers\Backpack',
    'as'         => 'backpack.'
], function(){
    Route::group([
        'prefix' => 'admin'
    ], function(){

        Route::crud('turbo-pages', 'TurboPageController');
        Route::crud('turbo-pages-content', 'TurboPageContentController');

        TurboPageContent::adminRoutes();

    });

});


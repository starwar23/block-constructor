<?php

use App\Core\Domain;
use Illuminate\Support\Facades\Route;

 /* Страница пред просмотра turbo-страниц */
 Route::get('turbo-page/{page}', 'TurboPageController@preview')->middleware('auth:web')->name('preview.turbo-page');




<?php

namespace App\Http\Controllers;

use App\Listeners\Bitrix24;
use App\Models\TurboPage\TurboPage;
use Illuminate\Http\Request;

class TurboPageController extends Controller {

    public function preview(TurboPage $page){

        debugbar()->disable();

        return view('app.turbo_page.page', [
            'page' => $page,
            'preview' => true,
        ]);
    }

    public function pixel(Request $request){
        $server = $request->server;
        $headers = collect($server->getHeaders());
        $ref = $headers->get('REFERER');
        $data = [];

        if(array_key_exists('query', parse_url($ref))) {
            parse_str(parse_url($ref)['query'], $data);
        }

        $request->merge($data);

        Bitrix24::checkUtm($request);

        return response()
            ->file(public_path('assets/img/design/turbo-pixel.gif'), [
                    'Access-Control-Max-Age: 0',
                    "Content-Type: image/gif"
                ]);
    }

}
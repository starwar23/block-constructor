<?php

namespace App\Jobs\TurboPagePublish;

use App\Core\Eloquent\Casts\ImageCast;
use App\Models\TurboPage\TurboPage;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MakeHtmlJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public function __construct(public TurboPage $page){
    }

    public function handle(){
        if($this->batch()->cancelled()){
            return;
        }

        $page = view('app.turbo_page.page', [
            'page' => $this->page,
        ]);

        $content = $this->optimize($page->render());

        Storage::disk('public_store')->put(ImageCast::getDestinationPath($this->page) . '/' . $this->page->url . '/index.html', $content);

        $this->page->update([
            'published_at' => now(),
        ]);
    }

    protected function optimize(string $buffer) : string{
        $replace = [
            "#\s+#su"     => ' ',
            "#>\s#su"      => '>',
            "#\s<#su"      => '<',
            "#>\s<#su"       => '><',
        ];

        return preg_replace(array_keys($replace), array_values($replace), $buffer);
    }
}
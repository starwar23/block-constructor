<?php

namespace App\Jobs;

use App\Jobs\TurboPagePublish\CompressImageJob;
use App\Jobs\TurboPagePublish\MakeHtmlJob;
use App\Models\TurboPage\TurboPage;
use App\Models\TurboPageContent;
use App\Traits\BatchedDispatchJobTrait;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use function Symfony\Component\String\s;

class TurboPagePublishJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable, BatchedDispatchJobTrait;

    public function __construct(public TurboPage $page){
    }

    public function handle(){
        if ($this->batch()->cancelled()) {
            return;
        }

        $imagesJobs = [];

        foreach($this->page->content as $item){
            /** @var TurboPageContent $item */
            if(!count($images = $item->content->getImages())) {
                continue;
            }

            //$image = [$src, $width, $height]
            foreach($images as $image){
                $imagesJobs[] = new CompressImageJob($this->page, $image);
            }
        }

        // add jobs to compress list of images
        $this->batch()->add($imagesJobs);

        $this->batch()->add([
            // add job to build and optimize html file
            new MakeHtmlJob($this->page),
        ]);
    }

    public static function getBatchName($args) : string{
        return "Публикация данных турбо страницы (URL: {$args[0]->url})";
    }
}
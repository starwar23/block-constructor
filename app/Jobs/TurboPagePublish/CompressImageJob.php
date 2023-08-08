<?php

namespace App\Jobs\TurboPagePublish;

use App\Models\TurboPage\TurboPage;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class CompressImageJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public function __construct(public TurboPage $page, public string $src, public ?int $width = null, public ?int $height = null)
    {}

    public function handle() {
        if ($this->batch()->cancelled()) {
            return;
        }

        $imagePath = Storage::disk('public')->path($this->src);
        $imageDir = File::dirname($this->src);
        $imageName = File::name($imagePath);
        $imageExtension = File::extension($imagePath);

        $image = Image::make($imagePath);

        $imageSavePath = Storage::disk('public')->path($this->page->publishPath()."/$imageDir/");

        try {
            if (!File::exists($imageSavePath)) {
                File::makeDirectory($imageSavePath, 0755, true);
            }
        } catch (Exception) {}

        $imageSavePath .= $imageName;

        $q = null;
        switch($imageExtension) {
            case 'png':
                $image->limitColors(255);
                break;
            case 'jpg':
            case 'jpeg':
                $q = 75;
                break;
        }

        if ($imageExtension == 'png') {
            // Set 8-bit color
            $image->limitColors(255);
        }

        $image
            ->save("$imageSavePath.$imageExtension", $q, $imageExtension)
            ->save("$imageSavePath.webp", $q, 'webp');

        //$image->destroy();
    }
}
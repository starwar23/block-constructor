<?php

namespace App\Models\TurboPage;

use App\Console\Kernel;
use App\Core\Eloquent\Casts\ImageCast;
use App\Jobs\TurboPagePublishJob;
use App\Models\TurboPageContent;
use App\Traits\CrudTrait;
use Carbon\Carbon;
use Illuminate\Bus\Batch;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Throwable;

/**
 * @property-read int $id
 * @property string   $name
 * @property string   $description
 * @property string   $url
 * @property bool     $is_published
 * @property Carbon   $published_at
 * @property Carbon   $created_at
 * @property Carbon   $updated_at
 * @property Carbon   $deleted_at
 *
 * @property string   $fullUrl
 */
class TurboPage extends Model {

    use HasSlug;
    use CrudTrait;

    protected $fillable = [
        'name',
        'url',
        'description',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'bool',
    ];

    protected $dates = [
        'published_at',
    ];

    public function content() : HasMany{
        return $this->hasMany(TurboPageContent::class)->with('content')->scopes(['sort']);
    }

    protected static function booted(){
        static::saving(function(self $page){
            if($page->is_published && $page->isDirty('url')){
                $page->rePublish();
            }

            if(!$page->is_published && $page->isDirty('is_published')){
                $page->removePublish();
                $page->forceFill(['published_at' => null]);
            }

            return $page->forceFill([
                'published_at' => $page->is_published ? now() : null,
            ]);
        });
        static::deleted(function(self $page){
            $page->removePublish();
        });
        static::deleting(function(self $page){
            $page->content->each(function($content){
                /** @var TurboPageContent $content */
                $content->content?->delete();
            });
        });
    }

    /* Подготовка стилей всех подключенных блоков */
    public function getCSS() : string{
        $commonCss = file_get_contents(public_path('assets/css/turbo_page.css'));

        return $this->content->map(function($pageContent){
            return $pageContent->content->cssPath();
        })->filter()->flip()->keys()->map(function($cssPath){
            $filePath = public_path($cssPath);

            return file_exists($filePath) ? file_get_contents($filePath) : '';
        })->prepend($commonCss)->implode('');
    }

    /* Подготовка javascript всех подключенных блоков */
    public function getJS() : string{
        return $this->content->map(function($pageContent){
            return $pageContent->content->jsPath();
        })->filter()->flip()->keys()->map(function($jsPath){
            $filePath = resource_path($jsPath);

            return file_exists($filePath) ? file_get_contents($filePath) : '';
        })->implode('');
    }

    /* Абсолютный адрес до html страницы */
    public function fullUrl() : Attribute{
        return Attribute::get(fn() => url($this->url));
    }

    public function getSlugOptions() : SlugOptions{
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('url');
    }

    /* Отправить страницу на публикацию */
    public function publish() : null|false|Batch {

        /* Сначала убираем с публикации */
        $this->clearPublishIndicator();

        try{
            $bachName = TurboPagePublishJob::getBatchName([$this]);

            if (DB::table('job_batches')
                ->where(fn($query) => $query
                    ->whereNull('cancelled_at')
                    ->whereNull('finished_at'))
                    ->where('name', $bachName)
                    ->exists()) {
                return false;
            }

            return Bus::batch(new TurboPagePublishJob($this))
                ->finally(function() {
                    $this->forceFill([
                        'is_published' => true,
                    ])->save();
                })
                ->catch(function() {
                    $this->clearPublishIndicator();
                })
                ->name($bachName)
                ->onQueue(Kernel::QUEUE_PROCESSING)
                ->dispatch();
        } catch (Throwable) {
            $this->removePublish();
            return null;
        }
    }

    public function publishPath() : string{
        return ImageCast::getDestinationPath($this) . '/' . $this->url;
    }

    /* Перепубликовать страницу */
    public function rePublish() : void{
        $this->removePublish();
        $this->publish();
    }

    /* Снять с публикации */
    public function removePublish(){
        if (Storage::disk('public_store')->exists($this->publishPath())) {
            Storage::disk('public_store')->deleteDirectory($this->publishPath());
        }
    }

    /* Убрать флаг публикации и удалить файлы страницы */
    public function clearPublishIndicator(){
        $this->forceFill([
            'is_published' => false,
        ])->save();
    }

    /* Поставить флаг публикации */
    public function setPublishIndicator(){
        $this->forceFill([
            'is_published' => true,
        ])->save();
    }

    /* Для админки */
    public function previewLink() : string{
        return '<a class="btn btn-sm btn-link" target="_blank" href="' . route('preview.turbo-page', ['page' => $this->id]) . '"><span class="icon voyager-eye" style="position: relative; top: 2px;"></span> Предпросмотр</a>';
    }

    /* Для админки */
    public function contentLink() : string{
        return '<a class="btn btn-sm btn-link" href="' . backpack_url('turbo-pages-content') . '?turbo-page=' . $this->id . '"><span class="icon voyager-file-text" style="position: relative; top: 2px;"></span> Контент</a>';
    }
}
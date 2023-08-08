<?php

namespace App\Models\TurboPage;

use App\Models\TurboPageContent;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

abstract class TurboPageContentAbstract extends BaseModel {

    public function __construct(array $attributes = []){
        $this->table = $this->tableName() ?? ("turbo_page_content_" . Str::plural(Str::snake(static::getBaseClassName())));
        parent::__construct($attributes);
    }

    /* Название блока на Русском языке (Для админки) */
    abstract static public function getBlockName() : string;

    /* Описание блока на Русском языке (Для админки) */
    abstract static public function getBlockDescription() : string;

    /* Список картинок используемых в блоке */
    abstract public function getImages() : array;

    /* Если нужно указать собственное имя таблицы */
    public function tableName() : ?string{
        return null;
    }

    public function blockName() : Attribute{
        return Attribute::get(fn() => static::getBlockName());
    }

    public function render($args) : string{
        extract($args);

        return view("app.turbo_page.blocks.{$this->getTemplateFileName()}", [
            'blockPage' => $this,
            'preview'   => $preview ?? false,
        ])->render();
    }

    public function getTemplateFileName() : string{
        /* Имя blade файла. Расположение файла: /resources/views/app/turbo_page/blocks */
        return $this->snakeClassName();
    }

    public function cssPath() : string{
        return "assets/css/turbo_pages/blocks/{$this->getCSSFileName()}.css";
    }

    public function jsPath() : string{
        return "assets/js/turboPage/blocks/{$this->getJSFileName()}.js";
    }

    /* Имя css файла. Расположение файла: /public/assets/css/turbo_pages/blocks/ */
    public function getCSSFileName() : string{
        return $this->kebabClassName();
    }

    /* Имя js файла. Расположение файла: /resources/assets/js/turboPage/blocks */
    public function getJSFileName() : string{
        return $this->kebabClassName();
    }

    public function adminSlug() : Attribute{
        return Attribute::get(function(){
            return static::getAdminSlug();
        });
    }

    public static function kebabClassName() : string{
        return Str::kebab(class_basename(static::class));
    }

    public static function snakeClassName() : string{
        return Str::snake(class_basename(static::class));
    }

    public function fullImagePath($image, $format = null, $debug = false) : string{
        if(!$debug){
            $dir = File::dirname($image);
            $name = File::name($image);
            $ext = $format ?? File::extension($image);

            return Storage::disk('public')->url($this->content->turboPage->publishPath() . "/$dir/$name.$ext");
        }else{
            return image_full($image);
        }
    }

    /* Slug страницы в админке */
    public static function getAdminSlug() : string{
        /* Имя blade файла. Расположение файла: /resources/views/app/turbo_page/blocks */
        return static::kebabClassName();
    }

    public function content() : MorphOne{
        return $this->morphOne(TurboPageContent::class, 'content');
    }

    public static function getBaseClassName() : string{
        return class_basename(get_called_class());
    }

    public static function getIdentifier() : string{
        return str_rot13(mb_strtolower(str_shuffle(static::getBaseClassName())));
    }

}
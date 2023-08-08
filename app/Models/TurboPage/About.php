<?php

namespace App\Models\TurboPage;

use App\Core\Eloquent\Casts\ImageCast;
use App\Traits\CrudTrait;
use App\Traits\ImageTrait;

/**
 *
 * @property-read int  $id
 * @property string    $name
 * @property string    $title
 * @property string    $description
 * @property string    $btn_link
 * @property string    $btn_text
 * @property ImageCast $image
 *
 */
class About extends TurboPageContentAbstract {

    use CrudTrait, ImageTrait;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'title',
        'description',
        'btn_link',
        'btn_text',
        'image',
    ];

    protected $casts = [
        'image' => ImageCast::class,
    ];

    public static function getBlockName() : string{
        return 'О компании 📑';
    }

    public static function getBlockDescription() : string{
        return 'Блок с именем, заголовком, коротким описанием, оранжевой кнопкой. Справа картинка';
    }

    public function getImages() : array{
        return [$this->image];
    }
}
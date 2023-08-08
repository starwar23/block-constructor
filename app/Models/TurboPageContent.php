<?php

namespace App\Models;

use App\Models\TurboPage\About;
use App\Models\TurboPage\Benefit;
use App\Models\TurboPage\BgBlock1;
use App\Models\TurboPage\DoorDiscount;
use App\Models\TurboPage\ElectronicSolution;
use App\Models\TurboPage\Image;
use App\Models\TurboPage\MainImage;
use App\Models\TurboPage\OurShowroom;
use App\Models\TurboPage\Pinterest;
use App\Models\TurboPage\PromotionalOffer;
use App\Models\TurboPage\RatingDoor;
use App\Models\TurboPage\Review;
use App\Models\TurboPage\Slider;
use App\Models\TurboPage\StripText;
use App\Models\TurboPage\TurboPage;
use App\Models\TurboPage\TurboPageContentAbstract;
use App\Traits\CrudTrait;
use App\Traits\Eloquent\SortOneScopeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Route;

/**
 * @property TurboPage                $turboPage
 * @property TurboPageContentAbstract $content
 */
class TurboPageContent extends Model {

    use SortOneScopeTrait, CrudTrait;

    public $timestamps = false;

    public static array $morphTypes = [
        'tpc_main_image'          => MainImage::class,
        'tpc_rating_door'         => RatingDoor::class,
        'tpc_bg_block1'           => BgBlock1::class,
        'tpc_promotional_offer'   => PromotionalOffer::class,
        'tpc_slider'              => Slider::class,
        'tpc_door_discount'       => DoorDiscount::class,
        'tpc_electronic_solution' => ElectronicSolution::class,
        'tpc_pinterest'           => Pinterest::class,
        'tpc_our_showroom'        => OurShowroom::class,
        'tpc_strip_text'          => StripText::class,
        'tpc_benefit'             => Benefit::class,
        'tpc_about'               => About::class,
        'tpc_image'               => Image::class,
        'tpc_review'              => Review::class,
    ];

    protected $fillable = [
        'turbo_page_id',
        'content_id',
        'content_type',
        'sort'
    ];

    public function content() : MorphTo{
        return $this->morphTo();
    }

    public function turboPage() : BelongsTo{
        return $this->belongsTo(TurboPage::class);
    }

    public function turboPageContentMainImage() : MorphMany{
        return $this->morphMany(MainImage::class, 'content');
    }

    public static function adminRoutes(){
        array_map(function($el){
            Route::crud('turbo-pages-content/' . $el::getAdminSlug(), "TurboPageContent{$el::getBaseClassName()}Controller");
        }, self::$morphTypes);
    }

    public function editContentButton() : string{
        return '<a href="/admin/turbo-pages-content/' . $this->content->adminSlug . '/' . $this->content->id . '/edit?turbo-page=' . $this->turboPage->id . '" class="btn btn-sm btn-link" data-button-type="delete"><i class="la la-trash"></i> Редактировать</a>';
    }

}
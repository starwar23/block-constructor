<?php

namespace App\Http\Controllers\Backpack;

use App\Core\Eloquent\Casts\ImageCast;
use App\Http\Requests\Backpack\TurboPageRequest;
use App\Models\TurboPage\TurboPage;
use App\Models\User;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations as OP;
use Exception;
use Illuminate\Bus\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Prologue\Alerts\Facades\Alert;

class TurboPageController extends CrudController {

    use OP\ListOperation, OP\CreateOperation, OP\UpdateOperation, OP\DeleteOperation;

    /**
     * @throws Exception
     */
    public function setup(){

        $this->crud->setModel(TurboPage::class);
        $this->crud->setEntityNameStrings('Turbo страницу', 'Turbo страницы');
        $this->crud->setRoute(backpack_url('turbo-pages'));

        $this->crud->setValidation(TurboPageRequest::class);

        if(!user_can(User::PERMISSION_EDIT_TURBO_PAGE)){
            $this->crud->denyAccess((array)'create');
            $this->crud->denyAccess((array)'update');
            $this->crud->denyAccess((array)'delete');
        }
        if(!user_can(User::PERMISSION_SEE_TURBO_PAGE)){
            $this->crud->denyAccess((array)'list');
        }

        $this->crud->addButtonFromModelFunction('line', 'preview', 'previewLink', 'beginning');
        $this->crud->addButtonFromModelFunction('line', 'content', 'contentLink', 'beginning');

        $this->crud->operation('list', function(){

            $this->crud->addButtonFromView('line', 'publish', 'publish-turbo-page', 'beginning');

            $this->crud->addColumns([
                [
                    'label' => 'Имя',
                    'name'  => 'name',
                    'type'  => 'text',
                ],
                [
                    'label'    => 'Размер',
                    'name'     => 'page_size',
                    'type'     => 'closure',
                    'function' => function(TurboPage $page){
                        if(Storage::disk('public_store')
                            ->exists(ImageCast::getDestinationPath($page) . '/' . $page->url . '/index.html')){
                            $size = Storage::disk('public_store')
                                ->size(ImageCast::getDestinationPath($page) . '/' . $page->url . '/index.html');

                            return "<span title='Размер страницы без изображений'>" . round($size / 1024) . "KB</span>";
                        }else{
                            if($page->is_published){
                                $page->clearPublishIndicator();
                            }
                            return "Не опубликовано!";
                        }
                    },
                ],
                [
                    'label' => 'Опубликовано',
                    'name'  => 'published_at',
                    'type'  => 'datetime',
                    'format' => 'DD MMMM GG в HH:mm',
                ],
                [
                    'label' => 'Ссылка',
                    'name'  => 'full_url',
                    'type'  => 'closure',
                    'function' => function(TurboPage $item) {
                        return "<a target='_blank' href=\"$item->fullUrl\">$item->fullUrl</a>";
                    },
                ],
            ]);
        });

        $this->crud->operation([
            'create',
            'update'
        ], function(){
            $this->crud->addFields([
                [
                    'label' => 'Опубликовано',
                    'name'  => 'is_published',
                    'type'  => 'boolean',
                ],
                [
                    'label' => 'Имя',
                    'name'  => 'name',
                    'type'  => 'text',
                ],
                [
                    'label' => 'Описание',
                    'name'  => 'description',
                    'type'  => 'textarea',
                    'hint'  => 'SEO description текст',
                ],
            ]);

        });
    }

    public static function setupPublishRoutes($segment, $routeName, $controller){
        Route::get("$segment/{page}/publish", [
            'as'        => "$routeName.publish",
            'uses'      => $controller . '@publish',
            'operation' => 'publish'
        ]);
    }

    /* Отправки страницы на публикацию */
    public function publish(Request $request, TurboPage $page) {
        $result = $page->publish();

        if ($result instanceof Batch) {
            Alert::success("Отправлено на публикацию 🚀")->flash();
        } elseif ($result === false) {
            Alert::info("Уже отправлено на публикацию ⌛")->flash();
        } else {
            Alert::error("Ошибка публикации ❌")->flash();
        }

        return redirect()->back();
    }

}

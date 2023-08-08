<?php

namespace App\Core\Traits;

use App\Models\TurboPage\TurboPage;
use App\Models\User;
use Backpack\CRUD\app\Http\Controllers\Operations as OP;
use Illuminate\Http\Request;
use Prologue\Alerts\Facades\Alert;

trait TurboPageContentTrait {

    use OP\CreateOperation {
        create as _create;
        store as _store;
    }

    use OP\UpdateOperation {
        edit as _edit;
        update as _update;
    }

    use OP\DeleteOperation;

    public ?TurboPage $turboPage;

    public function getFields() : array{
        return [];
    }

    abstract function getModel() : string;

    abstract function getValidationRequest() : ?string;

    public function setup(){

        $this->crud->setEntityNameStrings('Контент блок Turbo страницы', 'Контент блоки Turbo страниц');

        $this->crud->setModel($this->getModel());

        if($this->getValidationRequest()){
            $this->crud->setValidation($this->getValidationRequest());
        }

        $this->setTurboPage();
        $this->setBasicConfig();
    }

    public function setTurboPage() : void{
        $turboPageId = (int)request()->get('turbo-page');
        $this->turboPage = TurboPage::query()->findOrFail($turboPageId);
    }

    public function setBasicConfig() : void{

        $this->crud->setRoute(backpack_url('turbo-pages-content/' . $this->crud->model::kebabClassName()));

        $this->crud->denyAccess((array)'list');
        $this->crud->denyAccess((array)'delete');

        if(!user_can(User::PERMISSION_EDIT_TURBO_PAGE)){
            $this->crud->denyAccess((array)'update');
            $this->crud->denyAccess((array)'create');
        }

        $this->crud->operation([
            'create',
            'update'
        ], function(){
            $this->crud->addFields($this->getFields());
        });

        $this->crud->addField([
            'name'  => 'turbo-page-name',
            'label' => '',
            'type'  => 'custom_html',
            'value' => "<small>Turbo страница:</small> <h4>" . $this->turboPage->name . "</h4>",
        ]);
        $this->crud->addField([
            'name'  => 'turbo-page-content-name',
            'label' => '',
            'type'  => 'custom_html',
            'value' => "<small>Контент блок:</small> <h4>" . $this->crud->model->blockName . "</h4>",
        ]);
        $this->crud->addField([
            'name'  => 'turbo-page',
            'type'  => 'hidden',
            'value' => $this->turboPage->id,
        ]);
        $this->crud->addField([
            'name'  => 'sort',
            'label' => 'Позиция на странице',
            'type'  => 'view',
            'hint'  => 'Сортировка. Рекомендуемый шаг 10',
            'view'  => 'vendor/crud/fields/turbo_page_sort',
        ]);
    }

    public function edit($id){

        $this->crud->hasAccessOrFail('update');
        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;
        $this->crud->setOperationSetting('fields', $this->crud->getUpdateFields());
        // get the info for that entry

        $entry = $this->crud->getEntry($id);

        $this->data['entry'] = $entry;
        $this->data['entryRelation'] = $entry->content;
        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = [
            'active' => [
                'label' => 'Сохранить',
                'value' => 'save',
            ],
        ];

        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.edit') . ' ' . $this->crud->entity_name;

        $this->data['id'] = $id;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getEditView(), $this->data);
    }

    public function update(){

        $this->crud->hasAccessOrFail('update');

        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();
        // update the row in the db
        $item = $this->crud->update($request->get($this->crud->model->getKeyName()), $this->crud->getStrippedSaveRequest());
        $this->data['entry'] = $this->crud->entry = $item;

        $item->content()->update([
            'turbo_page_id' => $this->turboPage->id,
            'sort'          => $request->get('sort', 100),
        ]);

        // show a success message
        Alert::success(trans('backpack::crud.update_success'))->flash();

        return redirect(backpack_url('turbo-pages-content') . '?turbo-page=' . $this->turboPage->id);
    }

    public function create(){
        $this->crud->hasAccessOrFail('create');

        // prepare the fields you need to show
        $this->data['crud'] = $this->crud;
        $this->data['entryRelation'] = null;
        $this->data['saveAction'] = [
            'active' => [
                'label' => 'Сохранить',
                'value' => 'save',
            ],
        ];
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.add') . ' ' . $this->crud->entity_name;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getCreateView(), $this->data);
    }

    public function store(Request $request){

        $this->crud->hasAccessOrFail('create');

        // execute the FormRequest authorization and validation, if one is required
        $this->crud->validateRequest();

        $this->crud->setOperationSetting('saveAllInputsExcept', ['sort'], $this->crud->getOperation());

        // insert item in the db
        $item = $this->crud->create($this->crud->getStrippedSaveRequest());
        $this->data['entry'] = $this->crud->entry = $item;

        $item->content()->create([
            'turbo_page_id' => $this->turboPage->id,
            'sort'          => $request->get('sort', 100),
        ]);

        // show a success message
        Alert::success(trans('backpack::crud.insert_success'))->flash();

        return redirect(backpack_url('turbo-pages-content') . '?turbo-page=' . $this->turboPage->id);
    }
}
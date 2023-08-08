<?php

namespace App\Http\Controllers\Backpack;

use App\Core\Traits\TurboPageContentTrait;
use App\Models\TurboPage\About;
use Backpack\CRUD\app\Http\Controllers\CrudController;

class TurboPageContentAboutController extends CrudController {

    use TurboPageContentTrait;

    public function getModel(): string {
        return About::class;
    }

    public function getValidationRequest(): ?string {
        return null;
    }

    public function getFields(): array {
        return [
            [
                'name' => 'name',
                'label' => 'Имя',
                'type' => 'text',
                'hint' => 'Мелкий текст, слева вверху блока',
            ],
            [
                'name' => 'title',
                'label' => 'Заголовок',
                'type' => 'text',
            ],
            [
                'name' => 'description',
                'label' => 'Короткое описание',
                'type' => 'textarea',
            ],
            [
                'name' => 'btn_link',
                'label' => 'Ссылка кнопки',
                'type' => 'text',
            ],
            [
                'name' => 'btn_text',
                'label' => 'Текст на кнопке',
                'type' => 'text',
            ],
            [
                'name' => 'image',
                'label' => 'Картинка',
                'type' => 'image',
                'disk' => 'public_store',
            ],
        ];
    }
}

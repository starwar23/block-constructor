@php
    /** @var \App\Models\TurboPage\About $blockPage */
@endphp


<div class="about">
    <div class="about-wrapper">
        <div class="about-text-content">
            @if($blockPage->name)
                <div class="about-text-content__name">{!! $blockPage->name !!}</div>
            @endif

            <div class="about-text-content__title">{!! $blockPage->title !!}</div>

            @if($blockPage->description)
                <div class="about-text-content__description">{!! $blockPage->description !!}</div>
            @endif

            @if($blockPage->btn_link)
                <a href="{{ $blockPage->btn_link }}"
                   class="btn btn-primary">{!! $blockPage->btn_text ?? 'Подробнее' !!}</a>
            @endif
        </div>
        <div class="about-image-content">
            <picture>
                <source class="image" type="image/webp"
                        srcset="{{ $blockPage->fullImagePath($blockPage->image, 'webp', $preview) }}">
                <img class="image" src="{{ $blockPage->fullImagePath($blockPage->image) }}"
                     alt="{{ $blockPage->name }}">
            </picture>
        </div>
    </div>

</div>
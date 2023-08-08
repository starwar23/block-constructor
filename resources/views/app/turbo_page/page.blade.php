@php
    /** @var \App\Models\TurboPage\TurboPage $page */
@endphp

    <!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=yes, initial-scale=1.0, maximum-scale=5.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $page->name }}</title>

    <meta name="description" content="{{ $page->description }}">

    <style type="text/css">
        {{-- Стили каждого блока контента --}}
        {!! $page->getCSS() !!}
    </style>
</head>
<body>

{{--
    Проверка на поддержку формата изображений WebP. (Recomendate from Google Inc.)
    @see https://developers.google.com/speed/webp/faq#how_can_i_detect_browser_support_for_webp
--}}
<script type="text/javascript">
    var imgTestWebP = new Image();
    imgTestWebP.onload = function () {
        if (!(imgTestWebP.width > 0) && (imgTestWebP.height > 0)) {
            setIndicatorUnsupportedWebPImages();
        }
    };
    imgTestWebP.onerror = function () {
        setIndicatorUnsupportedWebPImages();
    };
    imgTestWebP.src = "data:image/webp;base64,UklGRiIAAABXRUJQVlA4IBYAAAAwAQCdASoBAAEADsD+JaQAA3AAAAAA";

    function setIndicatorUnsupportedWebPImages() {
        document.getElementsByTagName('body')[0].className = 'no-webp';
    }
</script>

<header>
    {{-- Верхнюю часть turbo-страницы подключим из файла с минимальным функционалом --}}
    @include('app.turbo_page.elements.header')
</header>

<main>
    {{-- HTML код каждого блока контента --}}
    @foreach($page->content as $pageContent)
        {!! $pageContent->content->render(get_defined_vars()) !!}
    @endforeach
    @include('app.turbo_page.elements.feedback_form')
</main>

<footer>
    {{-- Нижнию часть turbo-страницы возьмём с основного сайта --}}
    @include('app.turbo_page.elements.footer')
</footer>

@include('elements.country_currency')

<script type="text/javascript">
    {!! $page->getJS() !!}
</script>


</body>
</html>
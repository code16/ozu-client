@php use Code16\OzuClient\Eloquent\Media; @endphp
@props([
    /** @var Media $fileModel */
    'fileModel',
    'legend' => null,
])
<figure>
    <img src="{{ $fileModel->thumbnail($thumbnailWidth, $thumbnailHeight) }}" {{ $attributes }}>
    @if($legend)
        <figcaption>
            {{ $legend }}
        </figcaption>
    @endif
</figure>

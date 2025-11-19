@php
/**
 * @var \Code16\OzuClient\Eloquent\Media $media
 */
@endphp

<figure>
    <img src="{{ $media->thumbnail($thumbnailWidth, $thumbnailHeight) }}" {{ $attributes }}>
    @if($legend)
        <figcaption>
            {{ $legend }}
        </figcaption>
    @endif
</figure>

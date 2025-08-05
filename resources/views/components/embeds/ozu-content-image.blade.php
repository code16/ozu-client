@php
/**
 * @var \Code16\OzuClient\Eloquent\Media $fileModel
 */
@endphp

<figure>
    <img src="{{ $fileModel->thumbnail($thumbnailWidth, $thumbnailHeight) }}" {{ $attributes }}>
    @if($legend)
        <figcaption>
            {{ $legend }}
        </figcaption>
    @endif
</figure>

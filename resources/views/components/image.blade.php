@php
/**
 * @see \Code16\JockoClient\View\Components\Image
 */
@endphp

<p>
    <img src="{{ $image->thumbnail($thumbnailWidth, $thumbnailHeight) }}" {{ $attributes }}>
</p>

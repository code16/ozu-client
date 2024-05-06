@php
/**
 * @see \Code16\OzuClient\View\Components\Image
 */
@endphp

<p>
    <img src="{{ $image->thumbnail($thumbnailWidth, $thumbnailHeight) }}" {{ $attributes }}>
</p>

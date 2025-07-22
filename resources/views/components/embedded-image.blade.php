<figure>
    <img src="{{ $fileModel->thumbnail($thumbnailWidth, $thumbnailHeight) }}" {{ $attributes }}>
    @if($legend)
        <figcaption>
            {{ $legend }}
        </figcaption>
    @endif
</figure>

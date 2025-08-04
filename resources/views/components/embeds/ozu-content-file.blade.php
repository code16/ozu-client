@php
    /**
     * @var \Code16\OzuClient\Eloquent\Media $fileModel
     */
@endphp

<p>
    <a href="{{ $fileModel->downloadUrl() }}" download="{{ $name }}" target="_blank">
        {{ $legend ?? $name }}
    </a>
</p>

@props([
    'url',
    'legend' => null,
])
<div>
    <x-embed-video
        :url="$url"
        class=""
        :autoplay="false"
    />
    @if($legend)
        <p>{{$legend}}</p>
    @endif
</div>

@props([
    'quote',
    'author' => null,
])
<blockquote style="padding-left: 1rem; border-left: 3px solid #ccc;">
   {{$quote}}
    @if($author)
        <cite style="display: block;">
            <b>{{$author}}</b>
        </cite>
    @endif
</blockquote>

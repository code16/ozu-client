
@if(\Code16\JockoClient\Facades\Jocko::isLivePreview())
    <div style="background-color: #ffb; padding: .5rem; text-align: center">
        Prévisualisation du site. Pour appliquer les changements, rendez-vous dans la
        <a style="text-decoration: underline" href="{{ rtrim(config('jocko-client.api_host'), '/') }}/sharp/s-show/config" target="_blank">
            page configuration
        </a>.
    </div>
@endif

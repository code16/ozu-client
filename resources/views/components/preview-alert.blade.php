
@if(\Code16\JockoClient\Facades\Jocko::isPreview())
    <div style="background-color: #ffb; padding: .5rem; text-align: center; position: sticky; top: 0; z-index: 1000">
        Prévisualisation du site. Pour appliquer les changements, rendez-vous dans la
        <a style="text-decoration: underline" href="{{ rtrim(config('jocko-client.api_host'), '/') }}/sharp/s-show/deploy" target="_blank">
            page configuration
        </a>.
    </div>
@endif

import { Controller } from '@hotwired/stimulus';
import { Loader } from "@googlemaps/js-api-loader";
import { fromDecimalToFloat } from "@crozon/utils";

export default class extends Controller {
    static targets = ['lat', 'lng', 'map', 'suggestions'];
    static values = { apiKey: String, latCenter: String, lngCenter: String };

    private latTarget: HTMLInputElement;
    private lngTarget: HTMLInputElement;
    private suggestionsTarget: HTMLElement;
    private mapTarget: HTMLElement;

    private map: google.maps.Map;
    private loader: Loader;
    private geocoder: google.maps.Geocoder;

    private apiKeyValue: string;
    private latCenterValue: string;
    private lngCenterValue: string;
    private position: google.maps.LatLngLiteral;
    private positionMarker: google.maps.Marker;
    private suggestionMetadata: string;

    async connect() {
        this.handleMouseClick = this.handleMouseClick.bind(this);
        this.addMetadataToForm = this.addMetadataToForm.bind(this);

        this.suggestionMetadata = '';

        this.loader = new Loader({
            apiKey: this.apiKeyValue,
            version: 'weekly',
        });

        try {
            let google = await this.loader.load();
            this.geocoder = new google.maps.Geocoder();

            this.position = { lat: fromDecimalToFloat(this.latCenterValue), lng: fromDecimalToFloat(this.lngCenterValue) };

            this.map = new google.maps.Map(this.mapTarget, {
                center: this.position,
                zoom: 16,
            });

            this.positionMarker = new google.maps.Marker({
                position: this.position,
                map: this.map,
                title: 'Votre logement',
            });

            this.map.addListener('click', this.handleMouseClick);
        } catch (err: any) {
            this.mapTarget.innerText = 'Nous avons rencontré un problème pendant l\'affichage de la carte. Veuillez-nous contacter.';
        }
    }

    async addMetadataToForm(ev: Event) {
        ev.preventDefault();
        let element = document.createElement('textarea');
        element.name = 'suggestion_meta';
        element.classList.add('hidden');
        element.innerText = this.suggestionMetadata;

        this.element.appendChild(element);

        // @ts-ignore
        this.element.submit();
    }

    async addMetadataToElement(ev: Event) {
        ev.preventDefault();
        let target: HTMLInputElement = ev.target as HTMLInputElement;

        this.suggestionMetadata = target.getAttribute('data-meta');
    }

    async handleMouseClick(ev: google.maps.MapMouseEvent): Promise<void> {
        let { lat: latFn, lng: lngFn } = ev.latLng;

        let lat = latFn();
        let lng = lngFn();

        this.positionMarker.setPosition({ lat, lng });
        this.latTarget.setAttribute('value', lat.toString());
        this.lngTarget.setAttribute('value', lng.toString());

        let geocoding = await this.geocoder.geocode({ location: { lat, lng } });
        let { results } = geocoding;

        let stringAddresses = results.map((i, index) => `
<label for="suggestion-${index}">
    <input type="radio" value="${i.formatted_address}" id="suggestion-${index}" name="suggestion" data-meta='${JSON.stringify(i)}' data-action="input->form-types--map#addMetadataToElement" /> ${i.formatted_address}
</label>
        `).join('<br/>');

        this.suggestionsTarget.innerHTML = stringAddresses;
    }
}
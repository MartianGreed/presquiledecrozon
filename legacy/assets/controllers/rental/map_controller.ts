import { Controller } from '@hotwired/stimulus';
import { Loader } from '@googlemaps/js-api-loader';

export default class extends Controller {
    static targets = ['map'];
    static values = { apiKey: String, lat: Number, lng: Number };

    private map: google.maps.Map;
    private loader: Loader;
    private position: google.maps.LatLngLiteral;
    private positionMarker: google.maps.Marker;

    private apiKeyValue: string;
    private latValue: number;
    private lngValue: number;

    private mapTarget: HTMLElement;

    async connect() {
        this.loader = new Loader({
            apiKey: this.apiKeyValue,
            version: 'weekly',
        });

        try {
            let google = await this.loader.load();

            this.position = { lat: this.latValue, lng: this.lngValue };

            this.map = new google.maps.Map(this.mapTarget, {
                center: this.position,
                zoom: 16,
            });

            this.positionMarker = new google.maps.Marker({
                position: this.position,
                map: this.map,
                title: 'Votre logement',
            });

        } catch (err: any) {
            this.mapTarget.innerText = 'Nous avons rencontré un problème pendant l\'affichage de la carte. Veuillez-nous contacter.';
        }
    }

}
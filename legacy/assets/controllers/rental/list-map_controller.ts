import { Controller } from '@hotwired/stimulus';
import { Loader } from '@googlemaps/js-api-loader';
import { throttle } from 'lodash';


type RentalMarker = {
    id: string;
    lat: number;
    lng: number;
    amount: string;
    title: string;
}

export default class extends Controller {
    static targets = ['map'];
    static values = { apiKey: String, markers: Array };

    private mapTarget: HTMLElement;

    private apiKeyValue: string;
    private markersValue: Array<RentalMarker>;
    private loader: Loader;
    private map: google.maps.Map;
    private mapMarkers: Array<google.maps.Marker>;

    private toggledRental: HTMLElement;

    async connect() {
        this.toggledRental = null;
        this.loader = new Loader({
            apiKey: this.apiKeyValue,
            version: 'weekly',
        });

        try {
            let google = await this.loader.load();

            this.map = new google.maps.Map(this.mapTarget, {
                center: { lat: 48.25784292172176, lng: -4.424566142238213 },
                zoom: 11,
            });

            await this.initMarkers();
        } catch (err: any) {
            this.mapTarget.innerText = 'Nous avons rencontré un problème pendant l\'affichage de la carte. Veuillez-nous contacter.';
        }
    }

    private async initMarkers(): Promise<void> {
        this.mapMarkers = this.markersValue.map(i => {
            let marker = new google.maps.Marker({
                position: { lat: i.lat, lng: i.lng },
                map: this.map,
                title: i.title,
            });

            marker.addListener('mouseover', throttle((e) => {
                // Highlight the given rental
                // Scroll to it if it's not in view
                let item = document.getElementById(`rental-list-item-${i.id}`);
                if (item === this.toggledRental) {
                    return;
                }

                if (null !== this.toggledRental) {
                    this.toggledRental.classList.remove('toggled');
                }
                item.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' });
                this.toggledRental = item;
                this.toggledRental.classList.add('toggled');
            }, 500));

            return marker;
        })
    }
}
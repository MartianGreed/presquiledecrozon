import { ActionEvent, Controller } from '@hotwired/stimulus';
import axios from 'axios';

export default class extends Controller {
    static values = { uri: String, isFavoriteUri: String, isFavorite: Boolean };

    private uriValue: string;
    private isFavoriteUriValue: string;
    private isFavoriteValue: boolean;

    async connect(): Promise<void> {
        this.isFavoriteUriValueChanged = this.isFavoriteUriValueChanged.bind(this);
        this.addToFavoriteList = this.addToFavoriteList.bind(this);
    }

    async isFavoriteUriValueChanged(value: string): Promise<void> {
        let { data, status } = await axios.get(value);

        if (204 === status) {
            return;
        }

        this.isFavoriteValue = true;
    }

    async isFavoriteValueChanged(value: boolean): Promise<void> {
        if (!value) {
            this.element.classList.remove('is-favorite');
            return;
        }

        this.element.classList.add('is-favorite');
    }

    async addToFavoriteList(ev: ActionEvent): Promise<void> {
        ev.preventDefault();

        try {
            let { data, status } = await axios.post(this.uriValue);

            if (201 === status) {
                this.isFavoriteValue = true;
            }
            if (200 === status) {
                this.isFavoriteValue = false;
            }
        } catch (err) {
        }
    }
}
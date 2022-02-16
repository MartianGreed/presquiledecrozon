import { Controller } from '@hotwired/stimulus';
import axios from 'axios';
import {BookingRentalValues} from "../form-types/booking-rental_controller";

export default class extends Controller {
    static targets = ['price']
    static values = { api: String, form: Object };

    private priceTarget: HTMLElement;
    private apiValue: string;
    private formValue: BookingRentalValues;


    async connect() {
        let res = await axios.post(this.apiValue);
    }

    async formValueChanged(): Promise<void> {
        let { data, status, statusText } = await axios.post(this.apiValue, this.formValue);
        console.log(data, status, statusText)
    }

    private defaultContent(): string {
        return `Veuillez sélectionner les dates pour connaître le montant de la location.`
    }

    private totalContent(): string {
        return `Total <span data-booking--total-price-target="price"></span>`;
    }
}
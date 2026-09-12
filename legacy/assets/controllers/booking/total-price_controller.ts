import { Controller } from '@hotwired/stimulus';
import axios from 'axios';
import { BookingRentalValues } from '../form-types/booking-rental_controller';

export default class extends Controller {
    static targets = ['price']
    static values = { api: String, form: Object };

    private priceTarget: HTMLElement;
    private apiValue: string;
    private formValue: BookingRentalValues;


    async connect() {
        this.totalContent = this.totalContent.bind(this);
        this.defaultContent = this.defaultContent.bind(this);

        this.initFormValues();
    }

    async formValueChanged(): Promise<void> {
        if (0 === Object.keys(this.formValue).length) {
            return;
        }

        try {
            let { data, status, statusText } = await axios.post(this.apiValue, this.formValue);

            if (200 !== status) {
                return;
            }

            this.element.innerHTML = this.totalContent();
            this.priceTarget.innerText = data.booking_price;

        } catch (err) {}
    }

    private defaultContent(): string {
        return `Veuillez sélectionner les dates pour connaître le montant de la location.`
    }

    private totalContent(): string {
        return `Total <span data-booking--total-price-target="price"></span>`;
    }

    private initFormValues() {
        let form = document.querySelector('form[name="book_rental"]') as HTMLFormElement | null;
        if (null === form) {
            return;
        }

        this.formValue = {
            // @ts-ignore
            startAt: form.elements.namedItem('book_rental[startAt]')?.value,
            // @ts-ignore
            endAt: form.elements.namedItem('book_rental[endAt]')?.value,
            // @ts-ignore
            peopleCount: form.elements.namedItem('book_rental[peopleCount]')?.value,
        };
    }
}
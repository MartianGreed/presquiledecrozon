import { Controller } from '@hotwired/stimulus';

export type BookingRentalValues = {
    startAt: string;
    endAt: string;
    peopleCount: number;
}

export default class extends Controller {
    static targets = ['price'];

    private priceTarget: HTMLElement;

    get element(): HTMLFormElement {
        return super.element as HTMLFormElement;
    }

    async updateTotalPrice(ev: Event): Promise<void> {
        ev.preventDefault();
        let values = this.getFormValues(this.element, ['book_rental_startAt', 'book_rental_endAt', 'book_rental_peopleCount']);

        if (!Object.values(values).some(v => '' === v)) {
            this.priceTarget.setAttribute('data-booking--total-price-form-value', JSON.stringify(values));
        }
    }

    private getFormValues(form: HTMLFormElement, fields: Array<string>) {
        return Array.from(form.elements)
            .filter(e => fields.includes(e.id))
            .map((e: HTMLInputElement) => ({ [e.id.replace('book_rental_', '')]: e.value }))
            .reduce((prev, current, idx) => ({ ...prev, ...current }))
        ;
    }
}
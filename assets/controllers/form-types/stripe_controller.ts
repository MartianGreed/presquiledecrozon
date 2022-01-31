import { Controller } from '@hotwired/stimulus';
import { loadStripe } from '@stripe/stripe-js/pure';
import { Stripe, StripeElements, StripePaymentElement } from '@stripe/stripe-js';

export default class extends Controller {
    static targets = ['cardElements', 'error'];
    static values = { publicKey: String, secret: String, returnUrl: String };

    private stripe: Stripe;

    private cardElementsTarget: HTMLElement
    private errorTarget: HTMLElement

    private elements: StripeElements;
    private paymentElement: StripePaymentElement;

    private publicKeyValue: string;
    private secretValue: string;
    private returnUrlValue: string;

    public async connect() {
        this.confirmPayment = this.confirmPayment.bind(this);

        // @ts-ignore
        this.stripe = await loadStripe(this.publicKeyValue);
        this.elements = this.stripe.elements({
            clientSecret: this.secretValue
        });
        this.paymentElement = this.elements.create('payment');

        this.paymentElement.mount(this.cardElementsTarget);
    }

    public async confirmPayment(ev: Event) {
        ev.preventDefault();
        // @ts-ignore
        let { error } = await this.stripe.confirmPayment({
            elements: this.elements,
            confirmParams: {
                return_url: this.returnUrlValue,
                payment_method_data: {
                    billing_details: {
                        address: {
                            line1: this.getFormValue('create_subscription_address'),
                            line2: this.getFormValue('create_subscription_address2'),
                            postal_code: this.getFormValue('create_subscription_postalCode'),
                            city: this.getFormValue('create_subscription_town')
                        },
                        email: this.getFormValue('create_subscription_email'),
                        name: this.getFormValue('create_subscription_firstname') + ' ' + this.getFormValue('create_subscription_lastname'),
                        phone: this.getFormValue('create_subscription_phoneNumber'),
                    }
                }
            },
            redirect: 'always',
        });

        if (error) {
            this.errorTarget.textContent = error.message;
        }
    }

    private getFormValue(element: string): string {
        // @ts-ignore
        return this.element.elements[element].value;
    }
}
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'fieldContainer']

    private inputTarget: HTMLInputElement;
    private fieldContainerTarget: HTMLInputElement;
    private fieldCount: number;

    connect() {
        this.fieldCount = this.fieldContainerTarget.childElementCount
    }

    addItem(ev: Event) {
        ev.preventDefault();

        let value = this.inputTarget.value;
        if ('' === value) {
            return;
        }

        // @ts-ignore
        let prototype = String(this.element.dataset.prototype);

        let fieldFragment = prototype
            // @ts-ignore
            .replaceAll('__name__', this.fieldCount.toString())
        ;

        this.fieldContainerTarget.appendChild(
            document.createRange().createContextualFragment(fieldFragment)
        );

        let newInput: HTMLInputElement = this.fieldContainerTarget.lastElementChild.querySelector('input');
        newInput.setAttribute('value', value);
        this.inputTarget.value = '';
        ++this.fieldCount;
    }

    removeItem(ev: Event) {
        ev.preventDefault();

        // @ts-ignore
        this.fieldContainerTarget.removeChild(ev.target.parentNode)
    }
}
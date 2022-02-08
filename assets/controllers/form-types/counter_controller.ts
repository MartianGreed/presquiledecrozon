import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input'];
    static values = { updateField: String };

    private inputTarget: HTMLInputElement;
    private updateFieldValue: string;
    private updateField: HTMLElement;
    private fieldCount: number;

    async connect() {
        this.hydrateNode = this.hydrateNode.bind(this);

        if ("" === this.inputTarget.value) {
            this.inputTarget.value = "0";
        }

        if ('' !== this.updateFieldValue) {
            let updateField = document.querySelector(this.updateFieldValue);

            if (null !== updateField) {
                this.fieldCount = 0;
                this.updateField = updateField as HTMLElement;

                if (0 < updateField.children.length) {
                    try {
                        await this.hydrateNode()
                    } catch (err) {
                    }
                }
            }
        }

    }

    async increase(e: Event) {
        e.preventDefault();
        let value = parseInt(this.inputTarget.value)
        let newValue =  ++value

        this.inputTarget.value = newValue.toString();
        this.dispatchChangeCount(1);

        await this.handleUpdateField();
    }

    async decrease(e: Event) {
        e.preventDefault();
        let value = parseInt(this.inputTarget.value);

        if (1 > value) {
            return;
        }

        this.inputTarget.value = (value - 1).toString();
        this.dispatchChangeCount(-1);

        await this.handleDeleteField();
    }

    async handleUpdateField() {
        if (undefined === this.updateField) return;
        // this.updateField.appendChild()
        let prototype = String(this.updateField.dataset.prototype);

        let fieldFragment = prototype
            // @ts-ignore
            .replaceAll('__name__', this.fieldCount.toString())
            // @ts-ignore
            .replaceAll('__field_index__', (this.fieldCount + 1).toString())
        ;

        this.updateField.appendChild(
            document.createRange().createContextualFragment(fieldFragment)
        );

        ++this.fieldCount;
    }

    async handleDeleteField() {
        if (undefined === this.updateField || 0 >= this.fieldCount) return;

        this.updateField.removeChild(this.updateField.childNodes[this.updateField.childElementCount])

        --this.fieldCount;
    }

    dispatchChangeCount(value: number) {
        this.dispatch('changeCount', {
            detail: { value, },
            target: this.inputTarget,
            bubbles: true
        });
    }

    async hydrateNode(): Promise<void> {
        let childInputs = this.updateField.querySelectorAll('.bedroom__type__input');
        for (let i of Array.from(childInputs)) {

            if (undefined === i.querySelector) {
                continue;
            }

            let label = i.querySelector('label');

            label.innerText = label.innerText.replace('__field_index__', (this.fieldCount + 1).toString());

            ++this.fieldCount;
        }
    }
}
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['submitButton', 'image'];

    private submitButtonTarget: HTMLAnchorElement;
    private imageTargets: Array<HTMLElement>;

    async connect() {
        this.imageTargets.forEach(el => {
            if (el.classList.contains('no-file')) {
                this.submitButtonTarget.classList.add('disabled');
            }
        })
    }

    async updateFormState(ev: CustomEvent<{ isValid: boolean }>): Promise<void> {
        if (!ev.detail.isValid) {
            if (!this.submitButtonTarget.classList.contains('disabled')) {
                this.submitButtonTarget.classList.add('disabled');
            }
            return;
        }

        this.submitButtonTarget.classList.remove('disabled');
    }
}
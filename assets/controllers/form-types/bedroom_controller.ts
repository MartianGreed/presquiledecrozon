import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['fieldContainer', 'fieldToggler', 'bedCounter'];

    private fieldContainerTarget: HTMLElement;
    private fieldTogglerTarget: HTMLElement;
    private bedCounterTarget: HTMLElement;
    private currentBedCount: number;

    connect() {
        this.hydrateHTML = this.hydrateHTML.bind(this);
        this.currentBedCount = 0;

        this.hydrateHTML();
    }

    toggleFields(ev: Event) {
        ev.preventDefault();

        this.fieldContainerTarget.classList.toggle('hidden');
        let isHidden = this.fieldContainerTarget.classList.contains('hidden');

        this.fieldTogglerTarget.innerText = isHidden ? 'Définir le nombre de lits' : 'Terminé';
    }

    changeBedCount(ev: CustomEvent<{ value: number }>) {
        let { value } = ev.detail;

        this.currentBedCount += value;

        this.updateBedCountText(this.bedCounterTarget, this.currentBedCount);
    }

    private hydrateHTML() {
        let inputs = this.fieldContainerTarget.querySelectorAll('input');

        Array.from(inputs).forEach((input: HTMLInputElement) => {
            let bedCountItem = parseInt(input.value);

            if (0 >= bedCountItem) {
                return;
            }

            this.currentBedCount = this.currentBedCount + bedCountItem;
        });

        this.updateBedCountText(this.bedCounterTarget, this.currentBedCount);
    }

    private updateBedCountText(target: HTMLElement, value: number) {
        target.innerText = `${value} lit${value > 1 ? 's' : ''}`;
    }
}
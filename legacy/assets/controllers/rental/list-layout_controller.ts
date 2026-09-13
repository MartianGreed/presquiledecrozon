import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['toggler', 'listContainer'];

    private togglerTarget: HTMLInputElement;
    private listContainerTarget: HTMLElement;

    async connect() {
        this.handleTogglerClick = this.handleTogglerClick.bind(this);
        this.togglerTarget.addEventListener('click', this.handleTogglerClick)

        await this.initLayout();
    }

    private async handleTogglerClick(e: Event): Promise<void> {
        // @ts-ignore
        if (e.target.checked) {
            this.listContainerTarget.classList.toggle('map-toggled');
            return;
        }

        this.listContainerTarget.classList.remove('map-toggled');
    }

    private async initLayout(): Promise<void> {
        if (this.togglerTarget.checked) {
            this.listContainerTarget.classList.toggle('map-toggled');
            return;
        }

        this.listContainerTarget.classList.remove('map-toggled');
    }
}
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    async checkDisabled(ev: MouseEvent): Promise<void> {
        let clonedEvent = new MouseEvent('click', ev);
        ev.preventDefault();
        if (this.element.classList.contains('disabled')) {
            ev.stopPropagation();
            return;
        }

        ev.target.dispatchEvent(clonedEvent);
    }
}
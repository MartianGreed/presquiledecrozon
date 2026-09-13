import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = { maxItem: Number };

    private maxItemValue: number;
    public connect() {
        if (0 === this.maxItemValue) {
            this.maxItemValue = Infinity;
        }
    }

    async addPrototype(ev: Event): Promise<void> {
        ev.preventDefault();
        if (this.element.childElementCount > this.maxItemValue) {
            return;
        }

        // @ts-ignore
        let htmlString = this.element.dataset.prototype.replaceAll('__name__', this.element.childElementCount.toString());

        let fragment = document.createRange().createContextualFragment(htmlString);
        fragment.firstElementChild.innerHTML = this.deleteItemButton() + fragment.firstElementChild.innerHTML;
        this.element.appendChild(fragment);
    }

    async removeItem(ev: Event): Promise<void> {
        ev.preventDefault();

        let target = ev.currentTarget as HTMLElement;
        this.element.removeChild(target.parentElement);
    }

    private deleteItemButton(): string {
        return `<button type="button" data-action="form-types--collection#removeItem"><i class="fas fa-minus"></i></button>`
    }
}
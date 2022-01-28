import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    public connect() {
    }

    async addPrototype(ev: Event): Promise<void> {
        ev.preventDefault();

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
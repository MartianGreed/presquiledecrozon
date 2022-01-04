import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        toggle: String,
    };

    private nav: HTMLElement;
    private toggleValue: string;
    private isAnimating: boolean;

    connect() {
        this.isAnimating = false;
        this.nav = this.element.parentElement.parentElement.parentElement;
    }

    showMenu(ev: Event) {
        ev.preventDefault();
        let target = document.querySelector(this.toggleValue);

        target.classList.toggle('hidden');
        this.nav.classList.toggle('mobile-navigation-toggled');
        document.body.classList.toggle('mobile-navigation-toggled');
    }
}
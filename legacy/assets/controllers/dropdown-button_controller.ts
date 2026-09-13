import { Controller } from '@hotwired/stimulus';
import { enter, leave } from "@crozon/animation-utils";

export default class extends Controller {
    static targets = ['toggler', 'menu'];

    private togglerTarget: HTMLElement;
    private menuTarget: HTMLElement;
    private toggled: boolean;

    connect() {
        this.toggled = false;
    }

    handleClick(ev: Event) {
        this.toggled = !this.toggled;

        let callback = this.toggled ? 'show' : 'hide';

        this[callback]();
    }

    async show() {
        this.element.classList.add('toggled');
        await enter(this.menuTarget, 'appear');
    }

    async hide() {
        this.element.classList.remove('toggled');
        await leave(this.menuTarget, 'appear');
    }
}
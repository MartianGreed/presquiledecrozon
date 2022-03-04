import { ActionEvent, Controller } from '@hotwired/stimulus';
import { enter, leave } from '@crozon/animation-utils';

export default class extends Controller {
    static targets = ['menu', 'content'];

    private menuTarget: HTMLElement;
    private contentTarget: HTMLElement;
    private activeMenuItem: HTMLElement;
    private previousMenuItem: HTMLElement;
    private activeContentItem: HTMLElement;
    private previousContentItem: HTMLElement;

    async connect() {
        this.updateElementsClasses = this.updateElementsClasses.bind(this);

        this.activeMenuItem = this.menuTarget.firstElementChild as HTMLElement;
        this.previousMenuItem = this.activeMenuItem;

        if (null === this.activeMenuItem) {
            throw new Error('You have to create menu items inside menu target');
        }

        let { toggle }  = this.activeMenuItem.dataset;
        let contentItem = this.contentTarget.querySelector(`#${toggle}`) as HTMLElement;

        if (null === contentItem) {
            throw new Error('Content item has to exist for id ' + toggle)
        }

        this.activeContentItem = contentItem;
        this.previousContentItem = this.activeContentItem;

        await this.updateElementsClasses();
    }

    async toggleContent(ev: ActionEvent): Promise<void> {
         ev.preventDefault();
         let { target } = ev;

         if (this.previousMenuItem !== this.activeMenuItem) {
             this.previousMenuItem = this.activeMenuItem;
         }

         this.activeMenuItem = target as HTMLElement;
         let { toggle } = this.activeMenuItem.dataset;
        let contentItem = this.contentTarget.querySelector(`#${toggle}`) as HTMLElement;

        if (null === contentItem) {
            throw new Error('Content item has to exist for id ' + toggle)
        }

        if (this.previousContentItem !== this.activeContentItem) {
            this.previousContentItem = this.activeContentItem;
        }

        this.activeContentItem = contentItem;

        await this.updateElementsClasses();
    }

    private async updateElementsClasses(): Promise<void> {
        await leave(this.previousContentItem, 'appear');

        if (!this.activeMenuItem.classList.contains('active')) {
            this.activeMenuItem.classList.add('active');
        }
        if (!this.activeContentItem.classList.contains('active')) {
            this.activeContentItem.classList.toggle('active');
        }

        if (this.activeMenuItem !== this.previousMenuItem) {
            this.previousMenuItem.classList.remove('active');
        }

        if (this.activeContentItem !== this.previousContentItem) {
            this.previousContentItem.classList.remove('active');
        }
        await enter(this.activeContentItem, 'appear');
    }
}
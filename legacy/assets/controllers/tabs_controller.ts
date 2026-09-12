import { Controller, ActionEvent } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['selector', 'tabs'];

    private selectorTarget: HTMLElement;
    private tabsTarget: HTMLElement;

    private previousTab: HTMLElement;
    private content: HTMLElement;

    connect() {
        let selectedTab = this.selectorTarget.querySelector('.active') as HTMLElement | null;
        if (null !== selectedTab) {
            this.previousTab = selectedTab;
        }

        let content = this.tabsTarget.querySelector(`#${selectedTab.dataset.toggle}`) as HTMLElement | null;
        content.classList.add('active');

        this.content = content;
    }

    setCurrent(ev: ActionEvent): void {
        let target = ev.target as HTMLElement;

        this.previousTab.classList.remove('active');
        target.classList.add('active');
        this.previousTab = target;

        let newContent = this.tabsTarget.querySelector(`#${target.dataset.toggle}`) as HTMLElement | null;
        this.content.classList.remove('active');
        newContent.classList.add('active');
        this.content = newContent;
    }
}
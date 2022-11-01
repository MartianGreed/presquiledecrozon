import { Controller, ActionEvent } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['conversation'];

    private conversationTarget: HTMLElement;

    private selectedConversation: HTMLElement = null;

    connect() {
        let conversationSelectors = this.element.querySelectorAll('li.conversation__list__item');
        this.selectedConversation = conversationSelectors[0] as HTMLElement;
    }


    async selectConversation(ev: ActionEvent): Promise<void> {
        if (null !== this.selectedConversation) {
            this.selectedConversation.classList.remove('toggled');
        }

        let { params: { conversationId }} = ev;

        this.selectedConversation = ev.currentTarget as HTMLElement;
        this.selectedConversation.classList.add('toggled');

        if (conversationId === this.conversationTarget.getAttribute('data-conversation--detail-current-id-value')) {
            return;
        }

        this.conversationTarget.setAttribute('data-conversation--detail-current-id-value', conversationId);
    }
}
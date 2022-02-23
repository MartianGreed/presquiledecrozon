import { Controller, ActionEvent } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['conversation'];

    private conversationTarget: HTMLElement;

    async selectConversation(ev: ActionEvent): Promise<void> {
        let { params: { conversationId }} = ev;

        if (conversationId === this.conversationTarget.getAttribute('data-conversation--detail-current-id-value')) {
            return;
        }

        this.conversationTarget.setAttribute('data-conversation--detail-current-id-value', conversationId);
    }
}
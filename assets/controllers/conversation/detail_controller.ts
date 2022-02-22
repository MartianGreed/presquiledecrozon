import { Controller } from '@hotwired/stimulus';
import axios from 'axios';

type Message = {
    message: string;
    read_at: number|null;
    send_at: number;
    sender_id: string;
}

type Conversation = {
    full_name: string;
    period: string;
    people_count: number;
    total_price: string;
    messages: Array<Message>
}

export default class extends Controller {
    static values = { currentId: String, userId: String, apiUrl: String };
    static targets = ['name', 'period', 'peopleCount', 'price', 'messages'];

    private currentIdValue: string;
    private userIdValue: string;
    private apiUrlValue: string;
    private url: string;

    private nameTarget: HTMLElement;
    private periodTarget: HTMLElement;
    private peopleCountTarget: HTMLElement;
    private priceTarget: HTMLElement;
    private messagesTarget: HTMLElement;

    private conversation: Conversation;

    async connect() {
        this.url = this.apiUrlValue.replace('__id__', '');
    }

    async currentIdValueChanged(id: string): Promise<void> {
        let { data, status } = await axios.get(this.apiUrlValue.replace('__id__', id));
        if (200 !== status) {
            console.error('Failed to load conversation.');
            return;
        }

        this.conversation = data;
        this.conversation.messages = Array.from(Object.values(data.messages));

        this.updateView();
    }

    private updateView(): void {
        this.nameTarget.innerHTML = this.conversation.full_name;
        this.periodTarget.innerHTML = this.conversation.period;
        this.peopleCountTarget.innerHTML = this.conversation.people_count.toString();
        this.priceTarget.innerHTML = this.conversation.total_price;

        this.computeMessages();
    }

    private computeMessages(): void {
        this.messagesTarget.innerHTML = ''
        this.conversation.messages.forEach(m => {
            let readAt = null === m.read_at ? null : new Date(m.read_at);
            let child = document.createRange().createContextualFragment(this.messageItemTemplate(m.message, readAt, new Date(m.send_at), m.sender_id));
            this.messagesTarget.appendChild(child)
        })
    }

    private messageItemTemplate(message: string, readAt: Date|null, sendAt: Date, senderId: string): string
    {
        let isMe = this.userIdValue === senderId;
        return `<div class="conversation__detail__messages__item${isMe ? ' me' : ''}">${message}</div>`
    }
}
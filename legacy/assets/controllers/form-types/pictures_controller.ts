import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['container'];

    private containerTarget: HTMLElement;
    private pictureInputCount: number;

    async connect() {
        this.generateImageInput = this.generateImageInput.bind(this);

        this.pictureInputCount = this.containerTarget.childElementCount;
        if (0 === this.pictureInputCount) {
            for (let i = 0; i < 5; i++) {
                await this.generateImageInput();
            }
        }
    }

    async generateImageInput(): Promise<void> {
        let rawPrototype = this.containerTarget.getAttribute('data-prototype');
        // @ts-ignore
        let prototype = rawPrototype.replaceAll('__name__', this.pictureInputCount.toString());
        this.containerTarget.innerHTML = this.containerTarget.innerHTML + prototype;

        ++this.pictureInputCount;
    }
}
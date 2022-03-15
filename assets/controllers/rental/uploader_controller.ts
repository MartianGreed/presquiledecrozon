import {ActionEvent, Controller} from '@hotwired/stimulus';
import Dropzone from 'dropzone';

export default class extends Controller {
    static targets = ['dropzoneContainer'];
    static values = { uploadApiUri: String, field: String, index: Number };

    private dropzoneContainerTarget: HTMLElement;

    private uploadApiUriValue: string;
    private fieldValue: string;
    private indexValue: string;

    private dropzone: Dropzone;

    connect() {
        this.handleSending = this.handleSending.bind(this);
        this.removeEmptyClass = this.removeEmptyClass.bind(this);
        this.addEmptyClass = this.addEmptyClass.bind(this);

        try {
            this.dropzone = new Dropzone(this.dropzoneContainerTarget, {
                url: this.uploadApiUriValue,
                method: 'POST',
                withCredentials: true,
                // 5M
                maxFileSize: 5000000,
                paramName: 'upload_rental_picture[media][file][file]',
                addRemoveLinks: true,
                dictRemoveFile: 'x'
            });
        } catch (err) {
            console.error('Failed to create file uploader');
        }

        this.dropzone.on('sending', this.handleSending);
        this.dropzone.on('addedfile', this.removeEmptyClass);
        this.dropzone.on('removedfile', this.addEmptyClass)
    }

    async removeImage(ev: ActionEvent): Promise<void> {
        this.dropzoneContainerTarget.innerHTML = '';

        this.dropzoneContainerTarget.classList.add('no-file');
    }

    private handleSending(file: File, xhr: XMLHttpRequest, formData: FormData) {
        formData.set('upload_rental_picture[field]', this.fieldValue);
        if ('picture' === this.fieldValue) {
            formData.set('upload_rental_picture[index]', this.indexValue);
        }
    }

    private removeEmptyClass(file: File) {
        if (!this.dropzoneContainerTarget.classList.contains('no-file')) {
            return;
        }

        this.dropzoneContainerTarget.classList.remove('no-file');
    }

    private addEmptyClass(file: File) {
        if (this.dropzoneContainerTarget.classList.contains('no-file')) {
            return;
        }

        this.dropzoneContainerTarget.classList.add('no-file');
    }

}
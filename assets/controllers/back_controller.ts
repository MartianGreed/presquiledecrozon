import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    back(): void {
        window.history.back();
    }

}
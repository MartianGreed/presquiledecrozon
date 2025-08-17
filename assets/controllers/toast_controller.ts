import { Controller } from '@hotwired/stimulus';
import { Notyf } from 'notyf';

const ALLOWED_TYPES = ['success', 'error'];

export default class extends Controller {

  static values = { type: String }

  private content: string;
  private notyf: Notyf;
  private typeValue: string;

  initialize(): void {
    if (!this.validateType(this.typeValue)) {
      return;
    }
    this.notyf = new Notyf({
      duration: 5000,
      position: {
        x: 'right',
        y: 'top',
      },
      dismissible: true
    });
    this.content = this.element.innerHTML
    this.element.innerHTML = '';

    console.log(this.notyf[this.typeValue]({ message: this.content }));
  }

  private validateType(typeValue: string) {
    return ALLOWED_TYPES.includes(typeValue);
  }
}

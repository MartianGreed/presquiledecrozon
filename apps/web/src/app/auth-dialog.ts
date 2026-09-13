import {
  afterNextRender,
  Component,
  type ElementRef,
  output,
  viewChild,
} from "@angular/core";

@Component({
  selector: "crozon-auth-dialog",
  standalone: true,
  template: `<dialog #dialog class="auth-dialog" aria-labelledby="auth-title" (cancel)="dismiss.emit()" (keydown)="containFocus($event)"><button type="button" class="dialog-close" aria-label="Fermer la fenêtre" (click)="dismiss.emit()">×</button><ng-content /></dialog>`,
})
export class AuthDialog {
  readonly dismiss = output<void>();
  readonly dialog = viewChild.required<ElementRef<HTMLDialogElement>>("dialog");
  constructor() {
    afterNextRender(() => this.dialog().nativeElement.showModal());
  }
  containFocus(event: KeyboardEvent) {
    if (event.key !== "Tab") return;
    const elements = Array.from(
      this.dialog().nativeElement.querySelectorAll<HTMLElement>(
        "button:not(:disabled), input:not(:disabled), a[href], select:not(:disabled), textarea:not(:disabled)",
      ),
    ).filter((element) => element.getClientRects().length > 0);
    const target =
      event.shiftKey && document.activeElement === elements[0]
        ? elements.at(-1)
        : !event.shiftKey && document.activeElement === elements.at(-1)
          ? elements[0]
          : undefined;
    if (target) {
      event.preventDefault();
      target.focus();
    }
  }
}

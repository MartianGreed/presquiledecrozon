import { Component, computed, input, output, signal } from "@angular/core";

const dateKey = (date: Date) => date.toISOString().slice(0, 10);

@Component({
  selector: "crozon-stay-calendar",
  standalone: true,
  template: `<section class="stay-calendar" aria-label="Choisir les dates du séjour">
    <div class="calendar-heading"><button type="button" class="secondary" aria-label="Mois précédent" (click)="move(-1)" [disabled]="month()<=today.slice(0,7)">‹</button><h3 aria-live="polite">{{monthLabel()}}</h3><button type="button" class="secondary" aria-label="Mois suivant" (click)="move(1)">›</button></div>
    <div class="calendar-week" aria-hidden="true">@for(day of ['L','M','M','J','V','S','D']; track $index){<span>{{day}}</span>}</div>
    <div class="calendar-days">@for(day of days(); track day || $index){@if(day){<button type="button" [attr.aria-label]="label(day)" [attr.aria-pressed]="day===start()||day===end()" [class.in-range]="start()&&end()&&day>start()&&day<end()" [disabled]="day<today" (click)="choose(day)">{{+day.slice(8)}}</button>}@else{<span></span>}}</div>
    <p class="calendar-hint">{{start()&&!end()?'Choisissez votre date de départ.':'Choisissez votre arrivée, puis votre départ.'}}</p>
  </section>`,
})
export class StayCalendar {
  readonly start = input("");
  readonly end = input("");
  readonly rangeChange = output<{ start: string; end: string }>();
  readonly today = dateKey(new Date());
  readonly month = signal(this.today.slice(0, 7));
  readonly monthLabel = computed(() =>
    new Date(`${this.month()}-01T12:00:00Z`).toLocaleDateString("fr-FR", {
      month: "long",
      year: "numeric",
      timeZone: "UTC",
    }),
  );
  readonly days = computed(() => {
    const first = new Date(`${this.month()}-01T12:00:00Z`);
    const offset = (first.getUTCDay() + 6) % 7;
    const count = new Date(
      Date.UTC(first.getUTCFullYear(), first.getUTCMonth() + 1, 0),
    ).getUTCDate();
    return Array.from(
      { length: Math.ceil((offset + count) / 7) * 7 },
      (_, index) =>
        index < offset || index >= offset + count
          ? ""
          : `${this.month()}-${String(index - offset + 1).padStart(2, "0")}`,
    );
  });
  move(direction: number) {
    const date = new Date(`${this.month()}-01T12:00:00Z`);
    date.setUTCMonth(date.getUTCMonth() + direction);
    this.month.set(dateKey(date).slice(0, 7));
  }
  label(day: string) {
    return new Date(`${day}T12:00:00Z`).toLocaleDateString("fr-FR", {
      weekday: "long",
      day: "numeric",
      month: "long",
      year: "numeric",
      timeZone: "UTC",
    });
  }
  choose(day: string) {
    this.rangeChange.emit(
      !this.start() || this.end() || day <= this.start()
        ? { start: day, end: "" }
        : { start: this.start(), end: day },
    );
  }
}

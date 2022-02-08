import {Controller} from '@hotwired/stimulus';
import Calendar, {CalendarDay, CalendarMonth, CalendarView} from "@crozon/calendar";
import { removeItemAtIndex } from "@crozon/utils";
import {DateTime} from "luxon";

type Range = {
    start: string,
    end: string,
}

export default class extends Controller {
    static targets = ['container', 'input'];

    private calendar: Calendar;
    private calendarView: CalendarView;

    private containerTarget: HTMLElement;
    private inputTarget: HTMLElement;

    private rangeStart: string = null;
    private prototype: string;
    private rangeList: Array<Range> = [];

    public connect() {
        this.printCalendar = this.printCalendar.bind(this);
        this.printMonth = this.printMonth.bind(this);
        this.printDays = this.printDays.bind(this);
        this.printDayItem = this.printDayItem.bind(this);

        if (0 < this.inputTarget.childElementCount) {
            this.initRanges();
        }

        this.prototype = this.inputTarget.dataset.prototype;

        this.initCalendar();

        this.printCalendar();
    }

    public getPrev(e: Event) {
        e.preventDefault();
        let calendar = this.calendar.getPrev();
        let calendarView = calendar.getCalendarView();

        this.containerTarget.innerHTML = this.computeCalendar(calendarView);
        this.calendar = calendar;
        this.calendarView = calendarView;
    }

    public getNext(e: Event) {
        e.preventDefault();
        let calendar = this.calendar.getNext();
        let calendarView = calendar.getCalendarView();

        this.containerTarget.innerHTML = this.computeCalendar(calendarView);
        this.calendar = calendar;
        this.calendarView = calendarView;
    }

    public updateRange(e: Event) {
        e.preventDefault();
        let { target } = e;
        // @ts-ignore
        let date = target.dataset.date;

        // Remove existing range
        let startRangeIdx = this.rangeList.findIndex(r => r.start === date);
        if (-1 < startRangeIdx) {
            this.rangeList = removeItemAtIndex(startRangeIdx, this.rangeList);
            this.printCalendar();
            return;
        }

        // Update existing range
        let endRangeIdx = this.rangeList.findIndex(r => r.end === date);
        if (-1 < endRangeIdx) {
            let endRange = this.rangeList[endRangeIdx];
            this.rangeStart = endRange.start;
            this.rangeList = removeItemAtIndex(endRangeIdx, this.rangeList);
            this.printCalendar();
            return;
        }

        // Add range
        if (null !== this.rangeStart) {
            this.rangeList = [...this.rangeList, { start: this.rangeStart, end: date }];
            this.rangeStart = null;

            this.printCalendar();
            return;
        }

        // Start a new range
        if (null === this.rangeStart) {
            this.rangeStart = date;
        }

        this.printCalendar();
    }



    private initCalendar() {
        this.calendar = new Calendar(null, { months: 4 });
        this.calendarView = this.calendar.getCalendarView();
    }

    private computeCalendar(target: CalendarView): string {
        return `
<div class="calendar__container">
<div class="calendar__top">
    <button 
        type="button" 
        data-action="form-types--calendar#getPrev"
    >
        <i class="fas fa-chevron-left"></i>
    </button>
    <span>${target.year}</span>
    <button 
        type="button"
        data-action="form-types--calendar#getNext"
    >
        <i class="fas fa-chevron-right"></i>
    </button>
</div>
<div class="calendar__content">
    ${target.months.map(this.printMonth).join('')}
</div>
</div>
`;
    }

    private printCalendar(): void {
        this.containerTarget.innerHTML = this.computeCalendar(this.calendarView);

        this.syncForm();
    }

    private printMonth(month: CalendarMonth): string {
        return `
<div class="calendar__month__container">
    <div class="calendar__month__name">${month.label}</div>
    <div class="calendar__month__days">${month.days.map(this.printDays).join('')}</div>
</div>
        `;
    }

    private printDays(days: CalendarDay): string {
        return `
<div class="calendar__month__day-col">
    <div class="calendar__month__day-header">${days.label.toUpperCase()}</div>
${days.values.map(this.printDayItem).join('')}
</div>
        `;
    }

    private printDayItem(day): string {
        let classList = ['calendar__month__day-item'];
        if (!day.inMonth) {
            classList = [...classList, 'not-in-month'];
        }

        if(this.isInRange(day.formattedValue)) {
            classList = [...classList, 'in-range'];
        }

        if (this.isEdge(day.formattedValue)) {
            classList = [...classList, 'is-edge'];
        }

        return `
<div
    class="${classList.join(' ')}"
    data-action="click->form-types--calendar#updateRange"
    data-date="${day.formattedValue}"
>
    ${day.label}
</div>
`;
    }

    private isInRange(date: string): boolean {
        let dateObj = DateTime.fromFormat(date, 'dd/MM/yyyy');
        for (let i = 0; i < this.rangeList.length; i++) {
            let range = this.rangeList[i];
            if (
                dateObj > DateTime.fromFormat(range.start, 'dd/MM/yyyy')
                && dateObj < DateTime.fromFormat(range.end, 'dd/MM/yyyy')
            ) {
                return true;
            }
        }

        return false;
    }

    private isEdge(date: string): boolean {
        if (date === this.rangeStart) return true;

        return undefined !== this.rangeList.find(i => i.start === date || i.end === date);
    }

    private async syncForm(): Promise<void> {
        if (this.inputTarget.childElementCount === this.rangeList.length) return;
        this.inputTarget.innerHTML = '';
        this.rangeList.forEach(r => {
            // let inputContent = this.inputTarget.innerHTML;
            // @ts-ignore
            let fieldFragment = this.prototype.replaceAll('__name__', this.inputTarget.childElementCount);
            let fragment = document.createRange().createContextualFragment(fieldFragment);

            let inputs = fragment.querySelectorAll('input');
            inputs[0].setAttribute('value', r.start);
            inputs[1].setAttribute('value', r.end);

            this.inputTarget.appendChild(
                fragment
            );

            this.prototype = this.inputTarget.dataset.prototype;
        });
    }

    private initRanges(): void {
        let inputs = this.inputTarget.children;
        Array.from(inputs).forEach((i: Element) => {
            let fields = i.querySelectorAll('input');
            if (0 === fields.length) return;
            this.rangeList = [...this.rangeList, {
                start: fields[0].getAttribute('value'),
                end: fields[1].getAttribute('value'),
            }]
        });
    }
}
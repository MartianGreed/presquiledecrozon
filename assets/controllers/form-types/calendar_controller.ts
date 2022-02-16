import { Controller } from '@hotwired/stimulus';
import Calendar, { CalendarView, Range, computeCalendar } from '@crozon/calendar';
import { removeItemAtIndex } from '@crozon/utils';

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

        this.containerTarget.innerHTML = computeCalendar(calendarView, this.rangeList, 'form-types--calendar');
        this.calendar = calendar;
        this.calendarView = calendarView;
    }

    public getNext(e: Event) {
        e.preventDefault();
        let calendar = this.calendar.getNext();
        let calendarView = calendar.getCalendarView();

        this.containerTarget.innerHTML = computeCalendar(calendarView, this.rangeList, 'form-types--calendar');
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

    private printCalendar(): void {
        this.containerTarget.innerHTML = computeCalendar(this.calendarView, this.rangeList, 'form-types--calendar');

        this.syncForm();
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
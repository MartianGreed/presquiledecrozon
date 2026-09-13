import { Controller } from '@hotwired/stimulus';
import Calendar, { CalendarView, computeCalendar, Range } from '@crozon/calendar';

export default class extends Controller {
    static values = { dates: String };
    static targets = ['container'];

    private datesValue: string;

    private calendar: Calendar;
    private calendarView: CalendarView;

    private containerTarget: HTMLElement;

    private rangeList: Array<Range>;

    connect() {
        this.rangeList = JSON.parse(this.datesValue);


        this.initCalendar();
        this.printCalendar();
    }

    private initCalendar() {
        this.calendar = new Calendar(null, { months: 4, disablePassedDates: true });
        this.calendarView = this.calendar.getCalendarView();
    }

    private printCalendar(): void {
        this.containerTarget.innerHTML = computeCalendar(this.calendarView, this.rangeList, 'calendar-read');
    }

    public getPrev(e: Event) {
        e.preventDefault();
        let calendar = this.calendar.getPrev();
        let calendarView = calendar.getCalendarView();

        this.containerTarget.innerHTML = computeCalendar(calendarView, this.rangeList, 'calendar-read');
        this.calendar = calendar;
        this.calendarView = calendarView;
    }

    public getNext(e: Event) {
        e.preventDefault();
        let calendar = this.calendar.getNext();
        let calendarView = calendar.getCalendarView();

        this.containerTarget.innerHTML = computeCalendar(calendarView, this.rangeList, 'calendar-read');
        this.calendar = calendar;
        this.calendarView = calendarView;
    }
}
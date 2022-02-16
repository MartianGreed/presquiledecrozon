import { CalendarDay, CalendarMonth, CalendarView, DayItem, Range, isEdge, isInRange } from './index';

export function computeCalendar(target: CalendarView, rangeList: Array<Range>, controllerName: string): string {
    return `
<div class="calendar__container">
<div class="calendar__top">
    <button 
        type="button" 
        data-action="${controllerName}#getPrev"
    >
        <i class="fas fa-chevron-left"></i>
    </button>
    <span>${target.year}</span>
    <button 
        type="button"
        data-action="${controllerName}#getNext"
    >
        <i class="fas fa-chevron-right"></i>
    </button>
</div>
<div class="calendar__content">
    ${target.months.map(printMonth(rangeList)).join('')}
</div>
</div>
`;
}

export function printMonth(rangeList: Array<Range>): (month: CalendarMonth) => string {
    return function (month: CalendarMonth): string {
        return `
<div class="calendar__month__container">
    <div class="calendar__month__name">${month.label}</div>
    <div class="calendar__month__days">${month.days.map(printDays(rangeList)).join('')}</div>
</div>
        `;
    }
}

export function printDays(rangeList: Array<Range>): (days: CalendarDay) => string {
    return function (days: CalendarDay): string {
        return `
<div class="calendar__month__day-col">
    <div class="calendar__month__day-header">${days.label.toUpperCase()}</div>
${days.values.map(printDayItem(rangeList)).join('')}
</div>
        `;
    }
}

export function printDayItem(rangeList: Array<Range>): (day: DayItem) => string {
    return function (day: DayItem): string {
        let classList = ['calendar__month__day-item'];
        if (!day.inMonth) {
            classList = [...classList, 'not-in-month'];
        }

        if(isInRange(rangeList, day.formattedValue)) {
            classList = [...classList, 'in-range'];
        }

        if (isEdge(rangeList, day.formattedValue)) {
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
}
import { DateTime, Settings } from 'luxon';
export * from './printer';

Settings.defaultLocale = 'fr';

export type DayItem = {
    label: string,
    value: number,
    inMonth: boolean,
    formattedValue: string,
};

export type CalendarDay = {
    label: string,
    dayNumber: number,
    values: Array<DayItem>,
}

export type CalendarMonth = {
    days: Array<CalendarDay>,
    label: string,
    month: number,
}
export type CalendarView = {
    months: Array<CalendarMonth>,
    year: number,
};

export type CalendarOptions = {
    months: number,
}

export type Range = {
    start: string,
    end: string,
}

const DATE_SHORT = 'dd/MM/yyyy';

export default class Calendar {
    private date: DateTime
    private monthsView: number;
    private options: CalendarOptions;

    constructor(currentDate: string|null = null, options: CalendarOptions = null) {
        Settings.defaultLocale = 'fr';

        if ('caca' === currentDate) {
            throw new Error('Invalid date format');
        }

        if (null === options) {
            options = this.resolveOptions();
        }

        if (null === currentDate) {
            currentDate = DateTime.local().toFormat(DATE_SHORT);
        }

        this.date = DateTime.fromFormat(currentDate, DATE_SHORT);
        this.monthsView = options.months || 1;

        this.options = options;
    }

    getCalendarView(): CalendarView {
        return {
            year: this.date.year,
            months: this.generateMonths(),
        }
    }

    getDate(): string {
        return this.date.toFormat(DATE_SHORT);
    }

    getNext(): Calendar {
        return new Calendar(this.date.plus({ month: this.monthsView }).toFormat(DATE_SHORT), this.options);
    }

    getPrev(): Calendar {
        return new Calendar(this.date.minus({ month: this.monthsView }).toFormat(DATE_SHORT), this.options);
    }


    private generateMonths(): Array<CalendarMonth> {
        let months = [];
        let date = this.copyDateTime(this.date);
        for (let i = 0; i < this.monthsView; i++) {
            months = [...months, this.generateMonthItem(date)]
            date = date.plus({ month: 1 })
        }
        return months;
    }

    private generateMonthItem(date: DateTime): CalendarMonth {
        return {
            label: date.monthLong.toString(),
            month: date.month,
            days: this.generateDays(date.startOf('month')),
        };
    }

    private generateDays(monthDate: DateTime) {
        let days = this.getWeekView(monthDate);
        let date = this.copyDateTime(monthDate);
        let endOfMonth = this.copyDateTime(monthDate).endOf('month');
        if (1 < date.weekday) {
            date = date.plus({ days: (1 - date.weekday) });
        }

        if (endOfMonth.weekday <= 7) {
            endOfMonth = endOfMonth.plus({ days: 7 - endOfMonth.weekday });
        }

        while (date <= endOfMonth) {
            let dayItem = days.filter(d => d.dayNumber === date.weekday).pop();
             dayItem.values = [...dayItem.values, {
                 label: date.day.toLocaleString().padStart(2, '0'),
                 value: date.day,
                 inMonth: date.month === monthDate.month,
                 formattedValue: date.toFormat(DATE_SHORT),
             }];
             date = date.plus({ day: 1 });
        }

        return days;
    }

    private getWeekView(monthDate: DateTime): Array<CalendarDay> {
        let days = [];
        let date = this.copyDateTime(monthDate).startOf('week');

        for (let i = 1; i < 8; i++) {
            days = [...days, {
                label: date.weekdayShort.toString().substring(0, 1),
                dayNumber: i,
                values: [],
            }];
            date = date.plus({ day: 1 });
        }

        return days;
    }

    private resolveOptions(): CalendarOptions {
        return {
            months: 1,
        }
    }

    private copyDateTime(date: DateTime): DateTime {
        return DateTime.fromISO(date.toISO()).setLocale('fr-FR');
    }
}

export function isInRange(rangeList: Array<Range>, date: string): boolean {
    let dateObj = DateTime.fromFormat(date, 'dd/MM/yyyy');
    for (let i = 0; i < rangeList.length; i++) {
        let range = rangeList[i];
        if (
            dateObj > DateTime.fromFormat(range.start, 'dd/MM/yyyy')
            && dateObj < DateTime.fromFormat(range.end, 'dd/MM/yyyy')
        ) {
            return true;
        }
    }

    return false;
}

export function isEdge(rangeList: Array<Range>, date: string): boolean {
    return undefined !== rangeList.find(i => i.start === date || i.end === date);
}
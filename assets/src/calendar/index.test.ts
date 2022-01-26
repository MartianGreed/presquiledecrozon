import Calendar from './index';
const januarySample = require('./january-sample.json');

describe('Calendar', () => {

    it('Throws an error if date is not properly formatted', () => {
        expect(() => new Calendar('caca')).toThrow('Invalid date format');
    });

    it('It does not mutate the date', () => {
        expect((new Calendar('16/01/2022')).getDate()).toBe('16/01/2022');
    });

    it('Generates a CalendarView', () => {
        let calendar = new Calendar('16/01/2022');
        let calendarView = calendar.getCalendarView();

        expect(calendarView).toStrictEqual(januarySample);
    });

    it('Generates number of month asked', () => {
        let calendar = new Calendar(null, { months: 4 });
        let calendarView = calendar.getCalendarView();

        expect(calendarView.months.length).toBe(4);
    });

    it('Generates Previous and next months', () => {
        let calendar = new Calendar('03/02/2022');

        let next = calendar.getNext();
        let prev = calendar.getPrev();

        expect(next.getCalendarView().months[0].label).toBe('March');
        expect(prev.getCalendarView().months[0].label).toBe('January');
    })
})
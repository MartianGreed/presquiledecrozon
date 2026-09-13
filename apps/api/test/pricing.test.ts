import { describe, expect, test } from "bun:test";
import { emptyRental } from "../../../packages/contracts/src/models";
import { quote, validateDates, validateRental } from "../src/rentals/domain";

const rental = {
  ...emptyRental(),
  dailyRate: 10000,
  weeklyRate: 60000,
  cleaningTax: 3000,
  linensTax: 1000,
};
describe("calendar-night pricing", () => {
  test("one week uses one weekly price and taxes once", () =>
    expect(quote(rental, "2027-01-01", "2027-01-08")).toEqual({
      nights: 7,
      accommodation: 60000,
      cleaning: 3000,
      linens: 1000,
      total: 64000,
      currency: "EUR",
    }));
  test("eight nights use one week and one daily price", () =>
    expect(quote(rental, "2027-01-01", "2027-01-09").total).toBe(74000));
  test("cross-month stays count all nights", () =>
    expect(quote(rental, "2027-01-01", "2027-02-10").nights).toBe(40));
  test("season boundaries split rate groups", () =>
    expect(
      quote(
        {
          ...rental,
          rates: [
            {
              start: "2027-01-01",
              end: "2027-01-03",
              daily: 20000,
              weekly: 120000,
            },
          ],
        },
        "2027-01-01",
        "2027-01-05",
      ).accommodation,
    ).toBe(70000));
  test("invalid, reversed, empty and excessive ranges fail", () => {
    for (const [start, end] of [
      ["2027-02-30", "2027-03-05"],
      ["2027-01-01", "2027-01-01"],
      ["2027-02-01", "2027-01-01"],
      ["2027-01-01", "2029-01-01"],
    ])
      expect(() => validateDates(start!, end!)).toThrow();
  });
  test("overlapping rate ranges fail", () =>
    expect(() =>
      validateRental({
        ...rental,
        rates: [
          { start: "2027-01-01", end: "2027-01-05", daily: 1, weekly: 2 },
          { start: "2027-01-05", end: "2027-01-07", daily: 1, weekly: 2 },
        ],
      }),
    ).toThrow());
});

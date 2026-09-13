import type {
  Quote,
  RentalInput,
} from "../../../../packages/contracts/src/models";
import { assert, invalid } from "../errors";

const DAY = 86_400_000;
export function day(date: string): number {
  const value = Date.parse(`${date}T00:00:00Z`);
  assert(
    Number.isFinite(value) &&
      new Date(value).toISOString().slice(0, 10) === date,
    400,
    "date",
    "Date invalide.",
  );
  return value;
}
export function validateDates(start: string, end: string): number {
  const nights = (day(end) - day(start)) / DAY;
  assert(
    nights > 0 && nights <= 366,
    400,
    "date",
    "Le séjour doit durer de 1 à 366 nuits.",
  );
  return nights;
}
export function overlaps(
  a: { start: string; end: string },
  b: { start: string; end: string },
): boolean {
  return a.start < b.end && b.start < a.end;
}
export function validateRental(rental: RentalInput): void {
  for (const period of rental.unavailable)
    validateDates(period.start, period.end);
  for (const rate of rental.rates) {
    assert(
      day(rate.end) >= day(rate.start),
      400,
      "rate",
      "Période tarifaire invalide.",
    );
  }
  const rates = [...rental.rates].sort((a, b) =>
    a.start.localeCompare(b.start),
  );
  for (let i = 1; i < rates.length; i++)
    assert(
      rates[i - 1]!.end < rates[i]!.start,
      400,
      "rate",
      "Les périodes tarifaires se chevauchent.",
    );
}
export function quote(rental: RentalInput, start: string, end: string): Quote {
  const nights = validateDates(start, end);
  let accommodation = 0;
  let group: { daily: number; weekly: number; count: number } | undefined;
  const flush = () => {
    if (group)
      accommodation +=
        Math.floor(group.count / 7) * group.weekly +
        (group.count % 7) * group.daily;
  };
  for (let i = 0; i < nights; i++) {
    const date = new Date(day(start) + i * DAY).toISOString().slice(0, 10);
    const rate = rental.rates.find(
      (rate) => rate.start <= date && date <= rate.end,
    );
    const daily = rate?.daily ?? rental.dailyRate;
    const weekly = rate?.weekly ?? rental.weeklyRate;
    if (group && group.daily === daily && group.weekly === weekly)
      group.count++;
    else {
      flush();
      group = { daily, weekly, count: 1 };
    }
  }
  flush();
  const total = accommodation + rental.cleaningTax + rental.linensTax;
  if (!Number.isSafeInteger(total))
    throw invalid("Le montant dépasse la limite autorisée.");
  return {
    nights,
    accommodation,
    cleaning: rental.cleaningTax,
    linens: rental.linensTax,
    total,
    currency: "EUR",
  };
}
export function validatePreferences(
  rental: RentalInput,
  start: string,
  now: Date,
): void {
  const today = day(now.toISOString().slice(0, 10));
  const horizon = new Date(today);
  horizon.setUTCMonth(horizon.getUTCMonth() + rental.maxLeadMonths);
  assert(
    day(start) >= today + rental.minLeadDays * DAY &&
      day(start) <= horizon.getTime(),
    400,
    "preferences",
    "Ces dates ne respectent pas le délai de réservation du propriétaire.",
  );
}
export function completeStep(rental: RentalInput, step: string): void {
  switch (step) {
    case "configuration":
      assert(
        rental.bedrooms.length > 0 &&
          rental.bedrooms.every((b) => b.beds.length > 0),
        400,
        "configuration",
        "Ajoutez les chambres et couchages.",
      );
      break;
    case "description":
      assert(
        rental.title.trim().length >= 5 &&
          rental.description.trim().length >= 20,
        400,
        "description",
        "Renseignez un titre et une description.",
      );
      break;
    case "adresse":
      assert(
        rental.address.street.trim() &&
          rental.address.town.trim() &&
          rental.address.postcode.trim() &&
          rental.address.country.trim(),
        400,
        "address",
        "Complétez l’adresse.",
      );
      break;
    case "carte":
      assert(
        rental.latitude !== null && rental.longitude !== null,
        400,
        "location",
        "Indiquez la position du logement.",
      );
      break;
    case "photos":
      assert(
        rental.photos.length > 0,
        400,
        "photos",
        "Ajoutez au moins une photo.",
      );
      break;
    case "tarifs":
      assert(
        rental.dailyRate > 0 && rental.weeklyRate > 0,
        400,
        "prices",
        "Renseignez les tarifs.",
      );
      break;
  }
  validateRental(rental);
}

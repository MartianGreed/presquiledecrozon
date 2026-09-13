import { assert } from "../errors";
import { validateDates } from "./domain";

export interface RentalFilters {
  people?: number;
  type?: string;
  maxPrice?: number;
  start?: string;
  end?: string;
}

export function rentalFilters(params: URLSearchParams): RentalFilters {
  const result: RentalFilters = {};
  for (const key of ["people", "maxPrice"] as const) {
    const value = params.get(key);
    if (!value) continue;
    const number = Number(value);
    assert(
      Number.isSafeInteger(number) &&
        number >= 1 &&
        number <= (key === "people" ? 100 : 100_000_000),
      400,
      "filter",
      "Filtre de recherche invalide.",
    );
    result[key] = number;
  }
  const type = params.get("type");
  if (type) {
    assert(type.length <= 100, 400, "filter", "Type de logement invalide.");
    result.type = type;
  }
  const start = params.get("start"),
    end = params.get("end");
  if (start || end) {
    assert(start && end, 400, "date", "Choisissez une arrivée et un départ.");
    validateDates(start, end);
    assert(
      start >= new Date().toISOString().slice(0, 10),
      400,
      "date",
      "Choisissez une date d’arrivée à venir.",
    );
    result.start = start;
    result.end = end;
  }
  return result;
}

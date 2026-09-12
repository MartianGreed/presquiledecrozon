import { Schema as S } from "effect";

const text = (max: number) => S.String.pipe(S.maxLength(max));
const nonempty = (max: number) => text(max).pipe(S.minLength(1));
const integer = (min: number, max: number) =>
  S.Number.pipe(S.int(), S.between(min, max));
const cents = integer(0, 100_000_000);
export const Id = S.String.pipe(S.pattern(/^[A-Za-z0-9_-]{1,100}$/));
export const DateOnly = S.String.pipe(S.pattern(/^\d{4}-\d{2}-\d{2}$/));
const DateOrEmpty = S.Union(DateOnly, S.Literal(""));
const period = S.Struct({ start: DateOnly, end: DateOnly });
const time = S.String.pipe(S.pattern(/^([01]\d|2[0-3]):[0-5]\d$/));
export const ProfileSchema = S.Struct({
  firstname: nonempty(100),
  lastname: nonempty(100),
  cellphone: text(30),
  description: text(3000),
  preferredLanguage: nonempty(10),
  birthdate: DateOrEmpty,
  gender: text(20),
});
export const RentalSchema = S.Struct({
  title: text(255),
  description: text(20000),
  type: nonempty(100),
  peopleCount: integer(1, 100),
  bedrooms: S.Array(
    S.Struct({
      name: nonempty(100),
      beds: S.Array(
        S.Struct({ type: nonempty(100), count: integer(1, 20) }),
      ).pipe(S.maxItems(20)),
    }),
  ).pipe(S.maxItems(30)),
  equipment: S.Array(nonempty(100)).pipe(S.maxItems(100)),
  address: S.Struct({
    street: text(255),
    postcode: text(20),
    town: text(100),
    country: text(100),
  }),
  latitude: S.NullOr(S.Number.pipe(S.between(-90, 90))),
  longitude: S.NullOr(S.Number.pipe(S.between(-180, 180))),
  photos: S.Array(nonempty(500)).pipe(S.maxItems(30)),
  minLeadDays: integer(0, 365),
  maxLeadMonths: integer(1, 24),
  arrivalTime: time,
  departureTime: time,
  unavailable: S.Array(period).pipe(S.maxItems(200)),
  dailyRate: cents,
  weeklyRate: cents,
  rates: S.Array(
    S.Struct({ start: DateOnly, end: DateOnly, daily: cents, weekly: cents }),
  ).pipe(S.maxItems(100)),
  localTax: text(255),
  cleaningTax: cents,
  linensTax: cents,
  linens: S.Array(nonempty(100)).pipe(S.maxItems(50)),
  animalsAccepted: S.Boolean,
  smokingAllowed: S.Boolean,
  rules: S.Array(nonempty(1000)).pipe(S.maxItems(30)),
});
export const SaveRental = S.Struct({
  version: integer(1, 1_000_000),
  step: S.Literal(
    "configuration",
    "equipements",
    "description",
    "adresse",
    "carte",
    "photos",
    "disponibilites",
    "calendrier",
    "taxes",
    "tarifs",
    "conditions",
  ),
  rental: RentalSchema,
});
export const BookingRequest = S.Struct({
  rentalId: Id,
  start: DateOnly,
  end: DateOnly,
  peopleCount: integer(1, 100),
  message: nonempty(5000),
});
export const QuoteRequest = S.Struct({
  rentalId: Id,
  start: DateOnly,
  end: DateOnly,
  peopleCount: integer(1, 100),
});
export const MessageRequest = S.Struct({ body: nonempty(5000) });
export const PublishRequest = S.Struct({
  published: S.Boolean,
  version: integer(1, 1_000_000),
});
export const SubscriptionRequest = S.Struct({
  rentalId: Id,
  planId: Id,
  discountCode: text(100),
});
export const PlanSchema = S.Struct({
  id: Id,
  name: nonempty(100),
  amount: cents,
  months: integer(1, 120),
  active: S.Boolean,
});
export const DiscountSchema = S.Struct({
  payeeId: S.optional(S.NullOr(Id)),
  id: Id,
  code: nonempty(100),
  type: S.Literal("percent", "fixed"),
  amount: cents,
  expiresAt: DateOnly,
  maxUses: integer(1, 100000),
  uses: integer(0, 100000),
});
export const ReferenceSchema = S.Struct({ id: Id, name: nonempty(200) });

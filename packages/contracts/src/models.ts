export interface Profile {
  avatarUrl?: string;
  town?: string;
  firstname: string;
  lastname: string;
  cellphone: string;
  description: string;
  preferredLanguage: string;
  birthdate: string;
  gender: string;
}
export interface Persona {
  id: string;
  email: string;
  admin: boolean;
  profile: Profile | null;
}
export interface Period {
  start: string;
  end: string;
}
export interface Rate extends Period {
  daily: number;
  weekly: number;
}
export interface RentalInput {
  title: string;
  description: string;
  type: string;
  peopleCount: number;
  bedrooms: Array<{
    name: string;
    beds: Array<{ type: string; count: number }>;
  }>;
  equipment: string[];
  address: { street: string; postcode: string; town: string; country: string };
  latitude: number | null;
  longitude: number | null;
  photos: string[];
  minLeadDays: number;
  maxLeadMonths: number;
  arrivalTime: string;
  departureTime: string;
  unavailable: Period[];
  dailyRate: number;
  weeklyRate: number;
  rates: Rate[];
  localTax: string;
  cleaningTax: number;
  linensTax: number;
  linens: string[];
  animalsAccepted: boolean;
  smokingAllowed: boolean;
  rules: string[];
}
export interface Rental extends RentalInput {
  id: string;
  ownerId: string;
  slug: string;
  status: "draft" | "published" | "disabled" | "expired";
  version: number;
  completedSteps: string[];
  subscriptionExpiresAt: string | null;
}
export interface Quote {
  nights: number;
  accommodation: number;
  cleaning: number;
  linens: number;
  total: number;
  currency: "EUR";
}
export interface Booking {
  id: string;
  rentalId: string;
  ownerId: string;
  personaId: string;
  rentalTitle: string;
  start: string;
  end: string;
  peopleCount: number;
  status: "initialised" | "booked" | "confirmed" | "cancelled" | "done";
  quote: Quote;
  createdAt: string;
}
export interface Message {
  id: string;
  bookingId: string;
  personaId: string;
  body: string;
  createdAt: string;
}
export interface Notification {
  id: string;
  personaId: string;
  message: string;
  href: string;
  read: boolean;
  createdAt: string;
}
export interface Plan {
  id: string;
  name: string;
  amount: number;
  months: number;
  active: boolean;
}
export interface Discount {
  payeeId?: string | null;
  id: string;
  code: string;
  type: "percent" | "fixed";
  amount: number;
  expiresAt: string;
  maxUses: number;
  uses: number;
}
export interface Subscription {
  id: string;
  rentalId: string;
  personaId: string;
  planId: string;
  amount: number;
  months: number;
  status: "pending" | "paid" | "consumed";
  paymentIntentId: string | null;
  discountId: string | null;
  expiresAt: string | null;
}
export interface AccountSubscription extends Subscription {
  rentalTitle: string;
}
export interface Page<T> {
  items: T[];
  page: number;
  total: number;
}
export const rentalSteps = [
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
] as const;
export type RentalStep = (typeof rentalSteps)[number];
export function emptyRental(): RentalInput {
  return {
    title: "",
    description: "",
    type: "Maison",
    peopleCount: 2,
    bedrooms: [],
    equipment: [],
    address: { street: "", postcode: "", town: "", country: "France" },
    latitude: null,
    longitude: null,
    photos: [],
    minLeadDays: 1,
    maxLeadMonths: 12,
    arrivalTime: "16:00",
    departureTime: "10:00",
    unavailable: [],
    dailyRate: 0,
    weeklyRate: 0,
    rates: [],
    localTax: "Incluse",
    cleaningTax: 0,
    linensTax: 0,
    linens: [],
    animalsAccepted: false,
    smokingAllowed: false,
    rules: [],
  };
}

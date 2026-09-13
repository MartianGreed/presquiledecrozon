import { Schema as S } from "effect";

const text = (max: number) => S.String.pipe(S.maxLength(max));
const required = (max: number) => text(max).pipe(S.minLength(1));
export const ContentSchema = S.Struct({
  kind: S.Literal("event", "activity", "restaurant", "page"),
  slug: text(150).pipe(S.pattern(/^(?:[a-z0-9]+(?:-[a-z0-9]+)*)?$/)),
  title: required(200),
  summary: text(1000),
  body: text(30000),
  category: text(100),
  town: text(100),
  address: text(300),
  start: text(10),
  end: text(10),
  startTime: text(5),
  endTime: text(5),
  price: text(150),
  images: S.Array(text(200)).pipe(S.maxItems(12)),
  organizer: S.Struct({
    name: text(200),
    email: text(254),
    phone: text(30),
    website: text(1000),
  }),
  contactConsent: S.Boolean,
  publicationConsent: S.Boolean,
});
export const ProposalSchema = S.Struct({
  content: ContentSchema,
  submitter: S.Struct({
    firstname: required(100),
    lastname: required(100),
    email: required(254).pipe(S.pattern(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)),
    phone: required(30),
  }),
});
export const ContentWriteSchema = S.Struct({
  content: ContentSchema,
  status: S.Literal("draft", "pending", "published"),
  version: S.Number.pipe(S.int(), S.between(0, 2147483646)),
});

export type ContentKind = "event" | "activity" | "restaurant" | "page";
export interface ContentInput {
  kind: ContentKind;
  slug: string;
  title: string;
  summary: string;
  body: string;
  category: string;
  town: string;
  address: string;
  start: string;
  end: string;
  startTime: string;
  endTime: string;
  price: string;
  images: string[];
  organizer: { name: string; email: string; phone: string; website: string };
  contactConsent: boolean;
  publicationConsent: boolean;
}
export interface ContentEntry {
  id: string;
  version: number;
  status: "draft" | "pending" | "published";
  content: ContentInput;
  submitter?: {
    firstname: string;
    lastname: string;
    email: string;
    phone: string;
  };
}
export function emptyContent(kind: ContentKind = "event"): ContentInput {
  return {
    kind,
    slug: "",
    title: "",
    summary: "",
    body: "",
    category: "",
    town: "",
    address: "",
    start: "",
    end: "",
    startTime: "",
    endTime: "",
    price: "",
    images: [],
    organizer: { name: "", email: "", phone: "", website: "" },
    contactConsent: false,
    publicationConsent: false,
  };
}
export function contentPath(content: Pick<ContentInput, "kind" | "slug">) {
  const prefix = {
    event: "evenement",
    activity: "activite",
    restaurant: "restaurant",
    page: "informations",
  }[content.kind];
  return `/${prefix}/${content.slug}`;
}

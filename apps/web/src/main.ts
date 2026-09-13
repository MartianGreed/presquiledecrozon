import { bootstrapApplication } from "@angular/platform-browser";
import { provideRouter, withComponentInputBinding } from "@angular/router";
import { AppComponent } from "./app/app";
import { PageComponent } from "./app/page";

registerLocaleData(french);
bootstrapApplication(AppComponent, {
  providers: [
    { provide: LOCALE_ID, useValue: "fr-FR" },
    provideRouter(
      [{ path: "**", component: PageComponent }],
      withComponentInputBinding(),
    ),
  ],
}).catch(console.error);

import { registerLocaleData } from "@angular/common";
import french from "@angular/common/locales/fr";
import { LOCALE_ID } from "@angular/core";

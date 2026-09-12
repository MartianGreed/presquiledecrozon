import { bootstrapApplication } from "@angular/platform-browser";
import { provideRouter, withComponentInputBinding } from "@angular/router";
import { AppComponent } from "./app/app";
import { PageComponent } from "./app/page";

bootstrapApplication(AppComponent, {
  providers: [
    provideRouter(
      [{ path: "**", component: PageComponent }],
      withComponentInputBinding(),
    ),
  ],
}).catch(console.error);

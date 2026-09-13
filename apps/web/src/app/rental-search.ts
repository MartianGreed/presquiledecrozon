import { Component, input, type OnChanges, output } from "@angular/core";
import { FormsModule } from "@angular/forms";

export interface SearchFields {
  q: string;
  start: string;
  end: string;
  people: string;
  type: string;
  maxPrice: string;
}
export const emptySearch = (): SearchFields => ({
  q: "",
  start: "",
  end: "",
  people: "",
  type: "",
  maxPrice: "",
});

@Component({
  selector: "crozon-rental-search",
  standalone: true,
  imports: [FormsModule],
  template: `<form class="search-form segmented-search" (ngSubmit)="submit()">
    <label class="search-place"><span class="sr-only">{{label()}}</span><input name="place" [(ngModel)]="fields.q" placeholder="Ville ou logement"></label>
    <details class="search-filter"><summary>Dates <span aria-hidden="true">⌄</span></summary><div class="search-popover"><label>Arrivée recherchée<input type="date" name="arrival" [(ngModel)]="fields.start" [min]="today" [required]="!!fields.end"></label><label>Départ recherché<input type="date" name="departure" [(ngModel)]="fields.end" [min]="fields.start||today" [required]="!!fields.start"></label><button type="button" class="text-button" (click)="fields.start='';fields.end=''">Effacer les dates</button></div></details>
    <label class="search-select"><span class="sr-only">Personnes</span><select aria-label="Personnes" name="people" [(ngModel)]="fields.people"><option value="">Personnes</option>@for(count of [1,2,3,4,5,6,7,8,9,10,12,16,20];track count){<option [value]="count">{{count}} personne{{count>1?'s':''}}</option>}</select></label>
    <label class="search-select"><span class="sr-only">Type de logement recherché</span><select aria-label="Type de logement recherché" name="type" [(ngModel)]="fields.type"><option value="">Type de logement</option><option>Maison</option><option>Appartement</option><option>Gîte</option></select></label>
    <details class="search-filter"><summary>Prix <span aria-hidden="true">⌄</span></summary><div class="search-popover"><label>Prix de base maximum par nuit, en €<input type="number" name="price" [(ngModel)]="fields.maxPrice" min="0.01" max="1000000" step="0.01"></label><small>Hors tarifs saisonniers, ménage et linge. Le total du séjour est calculé sur l’annonce.</small></div></details>
    <button type="submit">Rechercher</button>
  </form>`,
})
export class RentalSearch implements OnChanges {
  readonly value = input<SearchFields>(emptySearch());
  readonly label = input("Ville ou logement");
  readonly searchChange = output<SearchFields>();
  readonly today = new Date().toISOString().slice(0, 10);
  fields = emptySearch();
  ngOnChanges() {
    this.fields = { ...this.value() };
  }
  submit() {
    this.searchChange.emit({ ...this.fields });
  }
}

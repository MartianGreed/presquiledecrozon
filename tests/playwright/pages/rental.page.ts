import { Page } from '@playwright/test';
import { BasePage } from './base.page';

export class RentalPage extends BasePage {
  constructor(page: Page) {
    super(page);
  }
  
  async goto(rentalSlug: string) {
    await super.goto(`/location/${rentalSlug}`);
  }
  
  async toggleFavorite() {
    await this.page.click('[data-favorite-toggle], button:has-text("Coup de coeur"), button:has-text("♥")');
    await this.page.waitForLoadState('networkidle');
  }
  
  async isFavorited(): Promise<boolean> {
    const favoriteButton = this.page.locator('[data-favorite-toggle], button:has-text("Coup de coeur")');
    const classes = await favoriteButton.getAttribute('class') || '';
    return classes.includes('active') || classes.includes('favorited') || classes.includes('text-red');
  }
  
  async getRentalTitle(): Promise<string | null> {
    const title = await this.page.locator('h1').first().textContent();
    return title;
  }
  
  async getRentalPrice(): Promise<string | null> {
    const price = await this.page.locator('[data-price], .rental-price, .price').first().textContent();
    return price;
  }
  
  async clickBooking() {
    await this.page.click('button:has-text("Réserver"), a:has-text("Réserver")');
    await this.page.waitForLoadState('networkidle');
  }
}
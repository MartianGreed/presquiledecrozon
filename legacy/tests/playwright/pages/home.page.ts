import { Page } from '@playwright/test';
import { BasePage } from './base.page';

export class HomePage extends BasePage {
  constructor(page: Page) {
    super(page);
  }
  
  async goto() {
    await super.goto('/');
  }
  
  async searchRentals(query: string) {
    await this.page.fill('input[placeholder*="Rechercher"], input[name="search"]', query);
    await this.page.press('input[placeholder*="Rechercher"], input[name="search"]', 'Enter');
    await this.page.waitForLoadState('networkidle');
  }
  
  async getRentalCards() {
    return await this.page.locator('[data-rental-id], .rental-card').all();
  }
  
  async clickRental(rentalId: string) {
    await this.page.click(`[data-rental-id="${rentalId}"], a[href*="/location/${rentalId}"]`);
    await this.page.waitForLoadState('networkidle');
  }
  
  async getPageTitle(): Promise<string> {
    return await this.page.title();
  }
}
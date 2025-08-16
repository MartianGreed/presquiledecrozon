import { Page } from '@playwright/test';
import { BasePage } from './base.page';

export class FavoritePage extends BasePage {
  constructor(page: Page) {
    super(page);
  }

  async goto() {
    await super.goto('/mon-compte/coups-de-coeur');
    // Wait for page to be fully loaded
    await this.page.waitForLoadState('domcontentloaded');
  }

  async getFavoriteCount(): Promise<number> {
    // Wait for either favorites to appear or empty message
    await this.page.waitForSelector('[data-rental-id], .favorite-item, .rental-card, text=/aucun.*coup.*coeur/i', { timeout: 5000 });
    const favorites = await this.page.locator('[data-rental-id], .favorite-item, .rental-card').all();
    return favorites.length;
  }

  async removeFavorite(rentalId: string) {
    // Wait for the API response when removing
    const responsePromise = this.page.waitForResponse(
      response => response.url().includes(`/mon-compte/coup-de-coeur/${rentalId}`) && 
                 response.status() >= 200 && response.status() < 300,
      { timeout: 5000 }
    );
    
    await this.page.click(`[data-rental-id="${rentalId}"] [data-favorite-toggle], [data-rental-id="${rentalId}"] button:has-text("Retirer")`);
    await responsePromise;
    
    // Wait for the element to be removed from DOM
    await this.page.waitForSelector(`[data-rental-id="${rentalId}"]`, { state: 'detached', timeout: 5000 });
  }

  async getFavoriteIds(): Promise<string[]> {
    // Ensure page is loaded first
    await this.page.waitForLoadState('domcontentloaded');
    const favorites = await this.page.locator('[data-rental-id]').all();
    const ids: string[] = [];
    for (const favorite of favorites) {
      const id = await favorite.getAttribute('data-rental-id');
      if (id) ids.push(id);
    }
    return ids;
  }

  async isEmpty(): Promise<boolean> {
    // Wait for either favorites or empty message to appear
    try {
      await this.page.waitForSelector('text=/aucun.*coup.*coeur/i, text=/pas.*favori/i', { timeout: 2000 });
      return true;
    } catch {
      return false;
    }
  }

  async waitForFavoriteToggle(rentalId: string): Promise<void> {
    await this.page.waitForResponse(
      response => response.url().includes(`/mon-compte/coup-de-coeur/${rentalId}`) && 
                 response.status() >= 200 && response.status() < 300,
      { timeout: 5000 }
    );
  }
}

import { Page } from '@playwright/test';
import { BasePage } from './base.page';

export class FavoritePage extends BasePage {
  constructor(page: Page) {
    super(page);
  }
  
  async goto() {
    await super.goto('/mes-coups-de-coeur');
  }
  
  async getFavoriteCount(): Promise<number> {
    const favorites = await this.page.locator('[data-rental-id], .favorite-item, .rental-card').all();
    return favorites.length;
  }
  
  async removeFavorite(rentalId: string) {
    await this.page.click(`[data-rental-id="${rentalId}"] [data-favorite-toggle], [data-rental-id="${rentalId}"] button:has-text("Retirer")`);
    await this.page.waitForLoadState('networkidle');
  }
  
  async getFavoriteIds(): Promise<string[]> {
    const favorites = await this.page.locator('[data-rental-id]').all();
    const ids: string[] = [];
    for (const favorite of favorites) {
      const id = await favorite.getAttribute('data-rental-id');
      if (id) ids.push(id);
    }
    return ids;
  }
  
  async isEmpty(): Promise<boolean> {
    return await this.page.locator('text=/aucun.*coup.*coeur/i, text=/pas.*favori/i').isVisible();
  }
}
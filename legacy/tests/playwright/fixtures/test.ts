import { test as base, Page } from '@playwright/test';
import { AuthPage } from '../pages/auth.page';
import { HomePage } from '../pages/home.page';
import { FavoritePage } from '../pages/favorite.page';
import { RentalPage } from '../pages/rental.page';
import { CreateRentalPage } from '../pages/create-rental.page';
import { DatabaseHelper } from '../helpers/database.helper';

type TestFixtures = {
  authPage: AuthPage;
  homePage: HomePage;
  favoritePage: FavoritePage;
  rentalPage: RentalPage;
  createRentalPage: CreateRentalPage;
  db: DatabaseHelper;
  waitForFavoriteState: (page: Page, selector: string, expectedState: string) => Promise<void>;
  waitForFavoriteToggle: (page: Page, rentalId: string) => Promise<void>;
};

export const test = base.extend<TestFixtures>({
  authPage: async ({ page }, use) => {
    await use(new AuthPage(page));
  },
  
  homePage: async ({ page }, use) => {
    await use(new HomePage(page));
  },
  
  favoritePage: async ({ page }, use) => {
    await use(new FavoritePage(page));
  },
  
  rentalPage: async ({ page }, use) => {
    await use(new RentalPage(page));
  },
  
  createRentalPage: async ({ page }, use) => {
    await use(new CreateRentalPage(page));
  },
  
  db: async ({}, use) => {
    const db = new DatabaseHelper();
    // Only clear favorites, not entire DB for each test
    await db.clearFavorites();
    await use(db);
  },

  waitForFavoriteState: async ({}, use) => {
    const waitForFavoriteState = async (page: Page, selector: string, expectedState: string) => {
      // Wait for Stimulus controller to process the response
      await page.waitForTimeout(1000);
      
      // Then check if the state has changed
      const currentState = await page.getAttribute(selector, 'data-rental--favorites-is-favorite-value');
      if (currentState !== expectedState) {
        // If not, wait a bit more and try again
        await page.waitForTimeout(1000);
        
        // Final check with waitForFunction
        await page.waitForFunction(
          ({ selector, expectedState }) => {
            const element = document.querySelector(selector);
            const currentState = element?.getAttribute('data-rental--favorites-is-favorite-value');
            return currentState === expectedState;
          },
          { selector, expectedState },
          { timeout: 5000, polling: 500 }
        );
      }
    };
    await use(waitForFavoriteState);
  },

  waitForFavoriteToggle: async ({}, use) => {
    const waitForFavoriteToggle = async (page: Page, rentalId: string) => {
      await page.waitForResponse(
        response => response.url().includes(`/mon-compte/coup-de-coeur/${rentalId}`) && 
                   response.status() >= 200 && response.status() < 300,
        { timeout: 5000 }
      );
    };
    await use(waitForFavoriteToggle);
  },
});

export { expect } from '@playwright/test';
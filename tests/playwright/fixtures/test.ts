import { test as base } from '@playwright/test';
import { AuthPage } from '../pages/auth.page';
import { HomePage } from '../pages/home.page';
import { FavoritePage } from '../pages/favorite.page';
import { RentalPage } from '../pages/rental.page';
import { DatabaseHelper } from '../helpers/database.helper';

type TestFixtures = {
  authPage: AuthPage;
  homePage: HomePage;
  favoritePage: FavoritePage;
  rentalPage: RentalPage;
  db: DatabaseHelper;
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
  
  db: async ({}, use) => {
    const db = new DatabaseHelper();
    await db.clearDatabase();
    await use(db);
  },
});

export { expect } from '@playwright/test';
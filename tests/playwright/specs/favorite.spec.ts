import { test, expect } from '../fixtures/test';

test.describe('Favorites', () => {
  test.beforeEach(async ({ db }) => {
    await db.clearDatabase();
    await db.loadFixtures();
  });

  test('persona can add rental to favorite list', async ({ authPage, homePage, favoritePage, page }) => {
    const email = 'adrienne.garnier@techer.net';
    const password = '123S3curedP4ssw0rd';
    
    await authPage.login(email, password);
    await homePage.goto();
    
    // Wait for rentals to load
    await page.waitForSelector('.rental_list__item');
    
    // Get first rental
    const firstRental = page.locator('.rental_list__item').first();
    const firstRentalId = await firstRental.getAttribute('id');
    expect(firstRentalId).toBeTruthy();
    
    const rentalId = firstRentalId!.replace('rental-list-item-', '');
    
    // Click heart button
    const heartButton = firstRental.locator('.rental_list__item__content__heart');
    expect(await heartButton.getAttribute('data-controller')).toBe('rental--favorites');
    
    await heartButton.click();
    
    // Wait for favorite state
    await expect(heartButton).toHaveClass(/is-favorite/);
    
    // Check favorites page
    await favoritePage.goto();
    await page.waitForSelector('.rental_list__item');
    
    const favoriteRental = page.locator(`#rental-list-item-${rentalId}`);
    await expect(favoriteRental).toBeVisible();
  });

  test('persona can remove rental from favorite list', async ({ authPage, homePage, page }) => {
    const email = 'theophile.dupre@denis.net';
    const password = '123S3curedP4ssw0rd';
    
    await authPage.login(email, password);
    await homePage.goto();
    
    // Wait for rentals to load
    await page.waitForSelector('.rental_list__item');
    
    // Get first rental and add to favorites
    const firstRental = page.locator('.rental_list__item').first();
    const heartButton = firstRental.locator('.rental_list__item__content__heart');
    
    await heartButton.click();
    await expect(heartButton).toHaveClass(/is-favorite/);
    
    // Remove from favorites
    await heartButton.click();
    await page.waitForTimeout(2000); // Wait for UI update
    
    await expect(heartButton).not.toHaveClass(/is-favorite/);
  });

  test('anonymous user cannot add rental to favorite list', async ({ homePage, page }) => {
    await homePage.goto();
    await page.waitForSelector('.rental_list__item');
    
    // Heart buttons should not have data-controller for non-logged users
    const heartButtons = page.locator('.rental_list__item__content__heart[data-controller="rental--favorites"]');
    await expect(heartButtons).toHaveCount(0);
  });

  test('persona can add multiple rentals to favorite list', async ({ authPage, homePage, favoritePage, page }) => {
    const email = 'ymasson@legall.com';
    const password = '123S3curedP4ssw0rd';
    
    await authPage.login(email, password);
    await homePage.goto();
    
    // Wait for rentals to load
    await page.waitForSelector('.rental_list__item');
    
    const rentals = page.locator('.rental_list__item');
    const rentalCount = await rentals.count();
    expect(rentalCount).toBeGreaterThanOrEqual(2);
    
    // Add first two rentals to favorites
    const firstHeartButton = rentals.nth(0).locator('.rental_list__item__content__heart');
    const secondHeartButton = rentals.nth(1).locator('.rental_list__item__content__heart');
    
    await firstHeartButton.click();
    await expect(firstHeartButton).toHaveClass(/is-favorite/);
    
    await secondHeartButton.click();
    await expect(secondHeartButton).toHaveClass(/is-favorite/);
    
    // Check favorites page
    await favoritePage.goto();
    await page.waitForSelector('.rental_list__item');
    
    const favoriteItems = page.locator('.rental_list__item');
    const favoriteCount = await favoriteItems.count();
    expect(favoriteCount).toBeGreaterThanOrEqual(2);
  });

  test('favorite list persists across sessions', async ({ authPage, homePage, favoritePage, page }) => {
    const email = 'gilles09@dijoux.org';
    const password = '123S3curedP4ssw0rd';
    
    await authPage.login(email, password);
    await homePage.goto();
    
    // Wait for rentals to load
    await page.waitForSelector('.rental_list__item');
    
    // Get first rental and add to favorites
    const firstRental = page.locator('.rental_list__item').first();
    const firstRentalId = await firstRental.getAttribute('id');
    const rentalId = firstRentalId!.replace('rental-list-item-', '');
    
    const heartButton = firstRental.locator('.rental_list__item__content__heart');
    await heartButton.click();
    await expect(heartButton).toHaveClass(/is-favorite/);
    
    // Logout
    await authPage.logout();
    
    // Login again
    await authPage.login(email, password);
    
    // Check favorites persist
    await favoritePage.goto();
    await page.waitForSelector('.rental_list__item');
    
    const favoriteRental = page.locator(`#rental-list-item-${rentalId}`);
    await expect(favoriteRental).toBeVisible();
  });
});
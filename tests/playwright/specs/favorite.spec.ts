import { test, expect } from '../fixtures/test';
import { VALID_USER } from '../helpers/persona.helper';


test.describe('Favorites', () => {
  test('persona can add rental to favorite list', async ({ authPage, homePage, favoritePage, page }) => {
    await authPage.login(VALID_USER.email, VALID_USER.password);
    await homePage.goto();

    // Wait for rentals to load
    await page.waitForSelector('.rental_list__item');

    // Get first rental
    const firstRental = page.locator('.rental_list__item').first();
    const firstRentalId = await firstRental.getAttribute('id');
    expect(firstRentalId).toBeTruthy();

    const rentalId = firstRentalId!;

    // Click heart button
    const heartButton = firstRental.locator('.rental_list__item__content__heart');
    expect(await heartButton.getAttribute('data-controller')).toBe('rental--favorites');

    // Check current favorite state
    const currentState = await heartButton.getAttribute('data-rental--favorites-is-favorite-value');

    // If already favorited, unfavorite first
    if (currentState === 'true') {
      await heartButton.click();
      await page.waitForTimeout(500);
    }

    // Now add to favorites
    await heartButton.click();
    await page.waitForLoadState('networkidle');

    // Wait for favorite state
    expect(await heartButton.getAttribute('data-rental--favorites-is-favorite-value')).toBe('true');
  });

  test('persona can remove rental from favorite list', async ({ authPage, homePage, page }) => {
    await authPage.login(VALID_USER.email, VALID_USER.password);
    await homePage.goto();

    // Wait for rentals to load
    await page.waitForSelector('.rental_list__item');

    // Get first rental
    const firstRental = page.locator('.rental_list__item').first();
    const heartButton = firstRental.locator('.rental_list__item__content__heart');

    const currentState = await heartButton.getAttribute('data-rental--favorites-is-favorite-value');

    if (currentState !== 'true') {
      await heartButton.click();
      await page.waitForTimeout(500);
      expect(await heartButton.getAttribute('data-rental--favorites-is-favorite-value')).toBe('true');
    }

    // Now remove from favorites
    await heartButton.click();
    await page.waitForTimeout(500);

    expect(await heartButton.getAttribute('data-rental--favorites-is-favorite-value')).toBe('false');
  });

  test('anonymous user cannot add rental to favorite list', async ({ homePage, page }) => {
    await homePage.goto();
    await page.waitForSelector('.rental_list__item');

    // Heart buttons should not have data-controller for non-logged users
    const heartButtons = page.locator('.rental_list__item__content__heart[data-controller="rental--favorites"]');
    await expect(heartButtons).toHaveCount(0);
  });

  test('persona can add multiple rentals to favorite list', async ({ authPage, homePage, favoritePage, page }) => {
    await authPage.login(VALID_USER.email, VALID_USER.password);
    await homePage.goto();

    // Wait for rentals to load
    await page.waitForSelector('.rental_list__item');

    const rentals = page.locator('.rental_list__item');
    const rentalCount = await rentals.count();
    expect(rentalCount).toBeGreaterThanOrEqual(2);

    // Get heart buttons for first two rentals
    const firstHeartButton = rentals.nth(0).locator('.rental_list__item__content__heart');
    const secondHeartButton = rentals.nth(1).locator('.rental_list__item__content__heart');

    // Check and set first rental to not favorited, then add to favorites
    const firstState = await firstHeartButton.getAttribute('data-rental--favorites-is-favorite-value');
    if (firstState === 'true') {
      await firstHeartButton.click();
      await page.waitForLoadState('networkidle');
    }
    await firstHeartButton.click();
    await page.waitForTimeout(500);
    expect(await firstHeartButton.getAttribute('data-rental--favorites-is-favorite-value')).toBe('true');

    // Check and set second rental to not favorited, then add to favorites
    const secondState = await secondHeartButton.getAttribute('data-rental--favorites-is-favorite-value');
    if (secondState === 'true') {
      await secondHeartButton.click();
      await page.waitForLoadState('networkidle');
    }
    await secondHeartButton.click();
    await page.waitForTimeout(500);
    expect(await secondHeartButton.getAttribute('data-rental--favorites-is-favorite-value')).toBe('true');
  });
});

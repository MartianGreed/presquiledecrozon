import { test, expect } from '../fixtures/test';
import { VALID_USER } from '../helpers/persona.helper';
import { AuthPage } from '../pages/auth.page';

// Configure this test file to run with a single worker to prevent race conditions

// Store test user credentials at module level
let testUserEmail: string;
let testUserPassword: string;

test.describe('Favorites', () => {
  test.beforeAll(async ({ browser }) => {
    // Create a unique test user for this test run
    const page = await browser.newPage();
    const authPage = new AuthPage(page);
    
    testUserEmail = `testuser${Date.now()}@test.test`;
    testUserPassword = VALID_USER.password;
    
    // Register the new user
    await authPage.register(testUserEmail, testUserPassword);
    
    // Clean up
    await page.close();
  });

  test.beforeEach(async ({ db, authPage }) => {
    // Clear favorites before each test for isolation
    await db.clearFavorites();
    // Login with the test user created in beforeAll
    await authPage.login(testUserEmail, testUserPassword);
  });

  test('persona can add rental to favorite list', async ({ homePage, page, waitForFavoriteState, waitForFavoriteToggle }) => {
    await homePage.goto();

    // Wait for rentals to load and Stimulus to initialize
    await page.waitForSelector('.rental_list__item');
    await page.waitForTimeout(1000); // Give Stimulus time to initialize controllers

    // Get first rental
    const firstRental = page.locator('.rental_list__item').first();
    const firstRentalId = await firstRental.getAttribute('id');
    expect(firstRentalId).toBeTruthy();

    const rentalId = firstRentalId!.replace('rental-list-item-', '');

    // Click heart button
    const heartButton = firstRental.locator('.rental_list__item__content__heart');
    const heartButtonSelector = `.rental_list__item:first-child .rental_list__item__content__heart`;

    // Verify it's the favorites controller
    expect(await heartButton.getAttribute('data-controller')).toBe('rental--favorites');

    // Check current favorite state
    const currentState = await heartButton.getAttribute('data-rental--favorites-is-favorite-value');

    // If already favorited, unfavorite first
    if (currentState === 'true') {
      const togglePromise = page.waitForResponse(
        response => response.url().includes(`/mon-compte/coup-de-coeur/${rentalId}`) &&
          response.status() >= 200 && response.status() < 300
      );
      await heartButton.click();
      await togglePromise;
      await page.waitForTimeout(1000); // Give Stimulus time to update
    }

    // Now add to favorites with proper wait
    const addPromise = page.waitForResponse(
      response => response.url().includes(`/mon-compte/coup-de-coeur/${rentalId}`) &&
        response.status() >= 200 && response.status() < 300
    );
    await heartButton.click();
    await addPromise;
    await page.waitForTimeout(500); // Give Stimulus time to update

    // For slower browsers, use a more lenient check
    await expect(async () => {
      const state = await heartButton.getAttribute('data-rental--favorites-is-favorite-value');
      expect(state).toBe('true');
    }).toPass({ timeout: 15000 });
  });

  test('persona can remove rental from favorite list', async ({ homePage, page, waitForFavoriteState, waitForFavoriteToggle }) => {
    await homePage.goto();

    // Wait for rentals to load and Stimulus to initialize
    await page.waitForSelector('.rental_list__item');
    await page.waitForTimeout(1000); // Give Stimulus time to initialize controllers

    // Get first rental
    const firstRental = page.locator('.rental_list__item').first();
    const firstRentalId = await firstRental.getAttribute('id');
    const rentalId = firstRentalId!.replace('rental-list-item-', '');

    const heartButton = firstRental.locator('.rental_list__item__content__heart');
    const heartButtonSelector = `.rental_list__item:first-child .rental_list__item__content__heart`;

    const currentState = await heartButton.getAttribute('data-rental--favorites-is-favorite-value');

    // Ensure it's favorited first
    if (currentState !== 'true') {
      const addPromise = page.waitForResponse(
        response => response.url().includes(`/mon-compte/coup-de-coeur/${rentalId}`) &&
          response.status() >= 200 && response.status() < 300
      );
      await heartButton.click();
      await addPromise;
      await page.waitForTimeout(1500); // Give Stimulus time to update
    }

    // Now remove from favorites with proper wait
    const removePromise = page.waitForResponse(
      response => response.url().includes(`/mon-compte/coup-de-coeur/${rentalId}`) &&
        response.status() >= 200 && response.status() < 300
    );
    await heartButton.click();
    await removePromise;
    await page.waitForTimeout(500); // Give Stimulus time to update

    // For slower browsers, use a more lenient check
    await expect(async () => {
      const state = await heartButton.getAttribute('data-rental--favorites-is-favorite-value');
      expect(state).toBe('false');
    }).toPass({ timeout: 15000 });
  });

  test('persona can add multiple rentals to favorite list', async ({ homePage, page, waitForFavoriteState, waitForFavoriteToggle }) => {
    await homePage.goto();

    // Wait for rentals to load and Stimulus to initialize
    await page.waitForSelector('.rental_list__item');
    await page.waitForTimeout(1000); // Give Stimulus time to initialize controllers

    const rentals = page.locator('.rental_list__item');
    const rentalCount = await rentals.count();
    expect(rentalCount).toBeGreaterThanOrEqual(2);

    // Process first rental
    const firstRental = rentals.nth(0);
    const firstRentalId = (await firstRental.getAttribute('id'))!.replace('rental-list-item-', '');
    const firstHeartButton = firstRental.locator('.rental_list__item__content__heart');
    const firstHeartSelector = `.rental_list__item:nth-child(1) .rental_list__item__content__heart`;

    // Clear first rental if needed
    const firstState = await firstHeartButton.getAttribute('data-rental--favorites-is-favorite-value');
    if (firstState === 'true') {
      const removePromise = waitForFavoriteToggle(page, firstRentalId);
      await firstHeartButton.click();
      await removePromise;
      await waitForFavoriteState(page, firstHeartSelector, 'false');
    }

    // Add first rental to favorites
    const addFirstPromise = waitForFavoriteToggle(page, firstRentalId);
    await firstHeartButton.click();
    await addFirstPromise;
    await waitForFavoriteState(page, firstHeartSelector, 'true');

    // Process second rental
    const secondRental = rentals.nth(1);
    const secondRentalId = (await secondRental.getAttribute('id'))!.replace('rental-list-item-', '');
    const secondHeartButton = secondRental.locator('.rental_list__item__content__heart');
    const secondHeartSelector = `.rental_list__item:nth-child(2) .rental_list__item__content__heart`;

    // Clear second rental if needed
    const secondState = await secondHeartButton.getAttribute('data-rental--favorites-is-favorite-value');
    if (secondState === 'true') {
      const removePromise = waitForFavoriteToggle(page, secondRentalId);
      await secondHeartButton.click();
      await removePromise;
      await waitForFavoriteState(page, secondHeartSelector, 'false');
    }

    // Add second rental to favorites
    const addSecondPromise = waitForFavoriteToggle(page, secondRentalId);
    await secondHeartButton.click();
    await addSecondPromise;
    await waitForFavoriteState(page, secondHeartSelector, 'true');

    // Verify both are favorited with lenient checks
    await expect(async () => {
      const firstState = await firstHeartButton.getAttribute('data-rental--favorites-is-favorite-value');
      const secondState = await secondHeartButton.getAttribute('data-rental--favorites-is-favorite-value');
      expect(firstState).toBe('true');
      expect(secondState).toBe('true');
    }).toPass({ timeout: 15000 });
  });
});

test.describe('Anonymous User Favorites', () => {
  test('anonymous user cannot add rental to favorite list', async ({ homePage, page }) => {
    await homePage.goto();
    await page.waitForSelector('.rental_list__item');

    // Heart buttons should not have data-controller for non-logged users
    const heartButtons = page.locator('.rental_list__item__content__heart[data-controller="rental--favorites"]');
    await expect(heartButtons).toHaveCount(0);
  });
});

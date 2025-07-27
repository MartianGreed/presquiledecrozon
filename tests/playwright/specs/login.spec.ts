import { test, expect } from '../fixtures/test';

test.describe('Login', () => {
  test.beforeEach(async ({ db }) => {
    await db.clearDatabase();
  });

  test('should display login page correctly', async ({ authPage, page }) => {
    await authPage.gotoLogin();
    
    await expect(page.locator('h1')).toContainText('Connexion');
    await expect(page.locator('input[id="inputEmail"]')).toBeVisible();
    await expect(page.locator('input[id="inputPassword"]')).toBeVisible();
    await expect(page.locator('button:has-text("Je me connecte")')).toBeVisible();
  });

  test('should login successfully with valid credentials', async ({ authPage, page }) => {
    const email = `user_${Date.now()}@example.com`;
    const password = 'TestPassword123!';
    
    await authPage.register(email, password);
    await authPage.login(email, password);
    
    await expect(page).not.toHaveURL(/\/login$/);
    await expect(page.locator('.text-red')).not.toBeVisible();
    expect(await authPage.isLoggedIn()).toBe(true);
  });

  test('should fail login with wrong password', async ({ authPage, page }) => {
    await authPage.login('nonexistent@example.com', 'WrongPassword123!');
    
    await expect(page).toHaveURL(/\/login$/);
    await expect(page.locator('.text-red')).toBeVisible();
  });

  test('should handle empty fields', async ({ authPage, page }) => {
    await authPage.gotoLogin();
    
    await page.fill('input[name="email"]', '');
    await page.fill('input[name="password"]', '');
    await page.click('button:has-text("Je me connecte")');
    
    await expect(page).toHaveURL(/\/login$/);
  });

  test('should handle invalid email format', async ({ authPage, page }) => {
    await authPage.gotoLogin();
    
    await page.fill('input[name="email"]', 'invalid-email');
    await page.fill('input[name="password"]', 'TestPassword123!');
    await page.click('button:has-text("Je me connecte")');
    
    await expect(page).toHaveURL(/\/login$/);
  });

  test('should logout successfully', async ({ authPage, page }) => {
    const email = `logout_test_${Date.now()}@example.com`;
    const password = 'TestPassword123!';
    
    await authPage.register(email, password);
    await authPage.login(email, password);
    
    expect(await authPage.isLoggedIn()).toBe(true);
    
    await authPage.logout();
    
    await expect(page).toHaveURL(/\/$/);
    expect(await authPage.isLoggedIn()).toBe(false);
  });

  test('should navigate to register and password reset pages', async ({ authPage, page }) => {
    await authPage.gotoLogin();
    
    // Navigate to register page
    await page.click('text="Je n\'ai pas de compte"');
    await expect(page.locator('h1')).toContainText('Créer mon compte');
    await expect(page).toHaveURL(/\/creer-mon-compte$/);
    
    // Navigate to password reset page
    await authPage.gotoLogin();
    await page.click('text="J\'ai oublié mon mot de passe"');
    await expect(page.locator('h1')).toContainText('Réinitialiser mon mot de passe');
    await expect(page).toHaveURL(/\/reinitialisation-mot-de-passe$/);
  });
});
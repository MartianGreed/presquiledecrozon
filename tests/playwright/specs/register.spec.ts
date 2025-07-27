import { test, expect } from '../fixtures/test';

test.describe('Registration', () => {
  test.beforeEach(async ({ db }) => {
    await db.clearDatabase();
  });

  test('should display registration page correctly', async ({ authPage, page }) => {
    await authPage.gotoRegister();
    
    await expect(page.locator('h1')).toContainText('Créer mon compte');
    await expect(page.locator('form[name="register_user"]')).toBeVisible();
    await expect(page.locator('input[id="register_user_email"]')).toBeVisible();
    await expect(page.locator('input[id="register_user_password_first"]')).toBeVisible();
    await expect(page.locator('input[id="register_user_password_second"]')).toBeVisible();
    await expect(page.locator('button:has-text("Je crée mon compte")')).toBeVisible();
  });

  test('should register successfully with valid data', async ({ authPage, page }) => {
    const uniqueEmail = `test_${Date.now()}@example.com`;
    const password = 'TestPassword123!';
    
    await authPage.register(uniqueEmail, password);
    
    // After successful registration, should redirect to login page
    await expect(page.locator('h1')).toContainText('Connexion');
    await expect(page).toHaveURL(/\/login$/);
  });

  test('should fail registration with existing email', async ({ authPage, page }) => {
    const existingEmail = `existing_${Date.now()}@example.com`;
    const password = 'TestPassword123!';
    
    // First create a user
    await authPage.register(existingEmail, password);
    
    // Now try to register with the same email
    await authPage.register(existingEmail, password);
    
    // Should stay on registration page with error
    await expect(page).toHaveURL(/\/creer-mon-compte$/);
    await expect(page.locator('.text-red-700')).toBeVisible();
    await expect(page.locator('.text-red-700')).toContainText('Impossible d\'utiliser cet email.');
  });

  test('should fail registration with password mismatch', async ({ authPage, page }) => {
    await authPage.gotoRegister();
    
    await page.fill('input[name="register_user[email]"]', `test_${Date.now()}@example.com`);
    await page.fill('input[name="register_user[password][first]"]', 'TestPassword123!');
    await page.fill('input[name="register_user[password][second]"]', 'DifferentPassword123!');
    await page.click('button:has-text("Je crée mon compte")');
    
    // Should stay on registration page with error
    await expect(page).toHaveURL(/\/creer-mon-compte$/);
    await expect(page.locator('.text-red-700')).toBeVisible();
    await expect(page.locator('.text-red-700')).toContainText('Les mots de passe doivent être identiques.');
  });

  test('should handle empty fields', async ({ authPage, page }) => {
    await authPage.gotoRegister();
    
    await page.fill('input[name="register_user[email]"]', '');
    await page.fill('input[name="register_user[password][first]"]', '');
    await page.fill('input[name="register_user[password][second]"]', '');
    await page.click('button:has-text("Je crée mon compte")');
    
    await expect(page).toHaveURL(/\/creer-mon-compte$/);
  });

  test('should handle invalid email format', async ({ authPage, page }) => {
    await authPage.gotoRegister();
    
    await page.fill('input[name="register_user[email]"]', 'invalid-email');
    await page.fill('input[name="register_user[password][first]"]', 'TestPassword123!');
    await page.fill('input[name="register_user[password][second]"]', 'TestPassword123!');
    await page.click('button:has-text("Je crée mon compte")');
    
    await expect(page).toHaveURL(/\/creer-mon-compte$/);
  });
});
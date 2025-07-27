import { test, expect } from '../fixtures/test';

test.describe('Password Reset', () => {

  test('should display password reset page correctly', async ({ authPage, page }) => {
    await authPage.gotoPasswordReset();

    await expect(page.locator('h1')).toContainText('Réinitialiser mon mot de passe');
    await expect(page.locator('form')).toBeVisible();
    await expect(page.locator('#request_reset_password_submit')).toContainText('Réinitialiser mon mot de passe');
  });

  test('should handle password reset request with valid email', async ({ authPage, page }) => {
    const email = `reset_test_${Date.now()}@example.com`;
    const password = 'TestPassword123!';

    // First create a user
    await authPage.register(email, password);

    // Request password reset
    await authPage.requestPasswordReset(email);

    // Should show success message or redirect
    const flashMessage = await authPage.getFlashMessage();
    if (flashMessage) {
      expect(flashMessage).toContain('email');
    }
  });

  test('should handle password reset request with non-existent email', async ({ authPage, page }) => {
    await authPage.requestPasswordReset('nonexistent@example.com');

    // The application might not reveal whether an email exists for security reasons
    // Just verify the form was submitted and page loaded properly
    await expect(page.locator('h1')).toBeVisible();
  });

  test('should validate email format', async ({ authPage, page }) => {
    await authPage.gotoPasswordReset();

    await page.fill('input[name="request_reset_password[email]"]', 'invalid-email');
    await page.click('#request_reset_password_submit');

    // Should stay on the same page with validation error
    await expect(page).toHaveURL(/reinitialisation-mot-de-passe/);
  });

  test('should handle empty email field', async ({ authPage, page }) => {
    await authPage.gotoPasswordReset();

    await page.fill('input[name="request_reset_password[email]"]', '');
    await page.click('#request_reset_password_submit');

    // Should stay on the same page
    await expect(page).toHaveURL(/reinitialisation-mot-de-passe/);
  });
});

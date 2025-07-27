import { Page } from '@playwright/test';
import { BasePage } from './base.page';

export class AuthPage extends BasePage {
  constructor(page: Page) {
    super(page);
  }
  
  async gotoLogin() {
    await this.goto('/login');
  }
  
  async gotoRegister() {
    await this.goto('/creer-mon-compte');
  }
  
  async gotoPasswordReset() {
    await this.goto('/reset-password/request');
  }
  
  async login(email: string, password: string) {
    await this.gotoLogin();
    await this.page.fill('input[name="email"]', email);
    await this.page.fill('input[name="password"]', password);
    await this.page.click('button:has-text("Je me connecte")');
    await this.page.waitForLoadState('networkidle');
  }
  
  async register(email: string, password: string) {
    await this.gotoRegister();
    await this.page.fill('input[name="register_user[email]"]', email);
    await this.page.fill('input[name="register_user[password][first]"]', password);
    await this.page.fill('input[name="register_user[password][second]"]', password);
    await this.page.click('button:has-text("Je crée mon compte")');
    await this.page.waitForLoadState('networkidle');
  }
  
  async requestPasswordReset(email: string) {
    await this.gotoPasswordReset();
    await this.page.fill('input[name="reset_password_request_form[email]"]', email);
    await this.page.click('button:has-text("Réinitialiser mon mot de passe")');
    await this.page.waitForLoadState('networkidle');
  }
  
  async isLoggedIn(): Promise<boolean> {
    // Check for user menu or logout button
    return await this.page.locator('a[href="/logout"], button:has-text("Déconnexion")').isVisible();
  }
  
  async logout() {
    if (await this.isLoggedIn()) {
      await this.page.click('a[href="/logout"], button:has-text("Déconnexion")');
      await this.page.waitForLoadState('networkidle');
    }
  }
}
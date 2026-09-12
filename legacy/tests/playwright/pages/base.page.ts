import { Page, Locator } from '@playwright/test';

export abstract class BasePage {
  protected page: Page;
  
  constructor(page: Page) {
    this.page = page;
  }
  
  async goto(path: string) {
    await this.page.goto(path);
  }
  
  async waitForLoadState() {
    await this.page.waitForLoadState('networkidle');
  }
  
  async takeScreenshot(name: string) {
    await this.page.screenshot({ path: `screenshots/${name}.png` });
  }
  
  async getFlashMessage(): Promise<string | null> {
    const flashMessage = this.page.locator('[role="alert"], .alert, .flash-message').first();
    if (await flashMessage.isVisible()) {
      return await flashMessage.textContent();
    }
    return null;
  }
  
  async acceptCookies() {
    const cookieButton = this.page.locator('text=/accept.*cookie/i');
    if (await cookieButton.isVisible()) {
      await cookieButton.click();
    }
  }
}
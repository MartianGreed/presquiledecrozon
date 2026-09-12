import { Page, Locator } from '@playwright/test';
import { BasePage } from './base.page';
import { RentalTestData } from '../helpers/rental.helper';

export class CreateRentalPage extends BasePage {
  constructor(page: Page) {
    super(page);
  }

  async goto() {
    await super.goto('/deposez-votre-annonce/configuration');
  }

  async gotoStep(step: string) {
    await super.goto(`/deposez-votre-annonce/${step}`);
  }

  async fillConfiguration(config: RentalTestData['configuration']) {
    // Select rental type - try different selectors
    const rentalTypeSelectors = [
      `input[type="radio"][value="${config.rentalType}"]`,
      `label:has-text("${config.rentalType}")`,
      `input[name*="rentalType"][value="${config.rentalType}"]`
    ];

    for (const selector of rentalTypeSelectors) {
      try {
        const element = this.page.locator(selector).first();
        if (await element.count() > 0) {
          await element.click();
          break;
        }
      } catch { }
    }

    // Set people count - look for actual input field
    const peopleInput = this.page.locator('input[name*="peopleCount"]').first();
    if (await peopleInput.count() > 0) {
      await peopleInput.fill(config.peopleCount.toString());
    } else {
      // Use counter buttons if input not found
      await this.setPeopleCount(config.peopleCount);
    }

    // Set bedroom count
    const bedroomInput = this.page.locator('input[name*="bedroomCount"]').first();
    if (await bedroomInput.count() > 0) {
      await bedroomInput.fill(config.bedroomCount.toString());
    } else {
      await this.setBedroomCount(config.bedroomCount);
    }

    // Configure bedrooms - simplified for now
    // Bedrooms might be dynamically added based on count
    await this.page.waitForTimeout(500); // Give time for dynamic fields
  }

  async fillFurnitures(furnitures: RentalTestData['furnitures']) {
    // Select standard furnitures - try different selectors
    for (const furniture of furnitures.selected) {
      const selectors = [
        `input[type="checkbox"][value="${furniture}"]`,
        `label:has-text("${furniture}")`,
        `input[name*="furnitures"][value="${furniture}"]`
      ];

      for (const selector of selectors) {
        try {
          const element = this.page.locator(selector).first();
          if (await element.count() > 0) {
            await element.click();
            break;
          }
        } catch { }
      }
    }

    // Add custom furnitures
    if (furnitures.custom.length > 0) {
      const customInput = this.page.locator('textarea[name*="customFurnitures"], input[name*="customFurnitures"]').first();
      if (await customInput.count() > 0) {
        await customInput.fill(furnitures.custom.join('\n'));
      }
    }
  }

  async fillDescription(description: RentalTestData['description']) {
    // Fill title
    const titleInput = this.page.locator('input[name*="title"]').first();
    if (await titleInput.count() > 0) {
      await titleInput.fill(description.title);
    }

    // Fill description
    const descInput = this.page.locator('textarea[name*="description"]').first();
    if (await descInput.count() > 0) {
      await descInput.fill(description.description);
    }
  }

  async fillAddress(address: RentalTestData['address']) {
    // Fill address
    const addressInput = this.page.locator('input[name*="address"]').first();
    if (await addressInput.count() > 0) {
      await addressInput.fill(address.address);
    }

    // Select town from dropdown or radio
    const townSelect = this.page.locator('select[name*="town"]').first();
    if (await townSelect.count() > 0) {
      try {
        await townSelect.selectOption({ label: address.town });
      } catch {
        // Try by value if label doesn't work
        await townSelect.selectOption(address.town);
      }
    } else {
      // If it's a radio button or checkbox
      const townLabel = this.page.locator(`label:has-text("${address.town}")`).first();
      if (await townLabel.count() > 0) {
        await townLabel.click();
      }
    }
  }

  async handleGeolocation() {
    // Check if there's a map or geolocation interface
    await this.page.waitForTimeout(500);

    // Try to find and click any geolocation fetch button
    const fetchButtonSelectors = [
      'button:has-text("Localiser")',
      'button:has-text("Récupérer")',
      'button[data-action="geolocate"]',
      'button.geolocate-btn'
    ];

    for (const selector of fetchButtonSelectors) {
      try {
        const button = this.page.locator(selector).first();
        if (await button.count() > 0 && await button.isVisible()) {
          await button.click();
          await this.page.waitForTimeout(2000); // Wait for map to load
          break;
        }
      } catch { }
    }

    // Try to accept suggested location if present
    const acceptButtonSelectors = [
      'button:has-text("Valider")',
      'button:has-text("Confirmer")',
      'button:has-text("Accepter")',
      'button[data-action="accept-location"]'
    ];

    for (const selector of acceptButtonSelectors) {
      try {
        const button = this.page.locator(selector).first();
        if (await button.count() > 0 && await button.isVisible()) {
          await button.click();
          await this.page.waitForTimeout(500);
          break;
        }
      } catch { }
    }

    // If there's a map but no buttons, just continue
    // The map step might just be informational
  }

  async uploadPictures(imagePaths: string[]) {
    // Check if there's a file input for cover image
    const coverInput = this.page.locator('input[type="file"][name*="cover"], input[type="file"]#cover');
    if (await coverInput.isVisible()) {
      await coverInput.setInputFiles(imagePaths[0]);
      await this.page.waitForTimeout(1000); // Wait for upload
    }

    // Upload additional pictures
    for (let i = 1; i < imagePaths.length && i < 6; i++) {
      const pictureInput = this.page.locator(`input[type="file"][name*="picture"], input[type="file"]#picture-${i}, input[type="file"]`).nth(i);
      if (await pictureInput.isVisible()) {
        await pictureInput.setInputFiles(imagePaths[i]);
        await this.page.waitForTimeout(1000); // Wait for upload
      }
    }

    // Alternative: if using drag and drop or AJAX upload
    const dropZone = this.page.locator('[data-dropzone], .dropzone, .upload-area');
    if (await dropZone.isVisible()) {
      for (const imagePath of imagePaths) {
        await this.page.locator('input[type="file"]').setInputFiles(imagePath);
        await this.page.waitForResponse(response =>
          response.url().includes('/rental/upload') && response.status() === 200
        );
      }
    }
  }

  async fillPreferences(preferences: RentalTestData['preferences']) {
    // These fields might be selects, radios, or inputs
    // Try to fill each preference if field exists

    try {
      await this.selectOption('acceptedLastBooking', preferences.acceptedLastBooking);
    } catch {
    }

    try {
      await this.selectOption('maxTimeBeforeBooking', preferences.maxTimeBeforeBooking);
    } catch {
    }

    try {
      await this.selectOption('beginBookingAt', preferences.beginBookingAt);
    } catch {
    }

    try {
      await this.selectOption('endBookingAt', preferences.endBookingAt);
    } catch {
    }
  }

  async fillCalendar(unavailableDates?: Array<{ start: string, end: string }>) {
    // If calendar widget is present, mark unavailable dates
    if (unavailableDates && unavailableDates.length > 0) {
      for (const period of unavailableDates) {
        // This would depend on the actual calendar implementation
        await this.page.click(`[data-date="${period.start}"]`);
        await this.page.click(`[data-date="${period.end}"]`);
      }
    }
    // Otherwise just proceed to next step
  }

  async fillTaxes(taxes: RentalTestData['taxes']) {
    // Fill local tax
    const localTaxInput = this.page.locator('input[name*="localTax"]').first();
    if (await localTaxInput.count() > 0) {
      await localTaxInput.fill(taxes.localTax.toString());
    }

    // Fill cleaning tax
    const cleaningTaxInput = this.page.locator('input[name*="cleaningTax"]').first();
    if (await cleaningTaxInput.count() > 0) {
      await cleaningTaxInput.fill(taxes.cleaningTax.toString());
    }

    // Fill linens tax
    const linensTaxInput = this.page.locator('input[name*="linensTax"]').first();
    if (await linensTaxInput.count() > 0) {
      await linensTaxInput.fill(taxes.linensTax.toString());
    }

    // Select linens if checkboxes present
    const linensCheckboxes = this.page.locator('input[type="checkbox"][name*="linens"]');
    const count = await linensCheckboxes.count();
    if (count > 0) {
      for (let i = 0; i < Math.min(count, 3); i++) {
        await linensCheckboxes.nth(i).check();
      }
    }
  }

  async fillPrices(prices: RentalTestData['prices']) {
    // Fill daily rate
    const dailyRateInput = this.page.locator('input[name*="dailyRate"]').first();
    if (await dailyRateInput.count() > 0) {
      await dailyRateInput.fill(prices.dailyRate.toString());
    }

    // Fill weekly rate
    const weeklyRateInput = this.page.locator('input[name*="weeklyRate"]').first();
    if (await weeklyRateInput.count() > 0) {
      await weeklyRateInput.fill(prices.weeklyRate.toString());
    }

    // Add seasonal prices
    for (let i = 0; i < prices.seasonalPrices.length; i++) {
      const season = prices.seasonalPrices[i];

      // Click add season button if needed
      const addButton = this.page.locator('button:has-text("Ajouter"), button:has-text("saison")');
      if (await addButton.isVisible() && i > 0) {
        await addButton.click();
      }

      // Fill seasonal price fields
      await this.page.locator(`input[name*="[prices][${i}][rangeStart]"], input[name*="rangeStart"]`).nth(i).fill(season.startDate);
      await this.page.locator(`input[name*="[prices][${i}][rangeEnd]"], input[name*="rangeEnd"]`).nth(i).fill(season.endDate);
      await this.page.locator(`input[name*="[prices][${i}][dailyRate]"], input[name*="seasonDailyRate"]`).nth(i).fill(season.dailyRate.toString());
      await this.page.locator(`input[name*="[prices][${i}][weeklyRate]"], input[name*="seasonWeeklyRate"]`).nth(i).fill(season.weeklyRate.toString());
    }
  }

  async fillConditions(conditions: RentalTestData['conditions']) {
    // Handle animals accepted
    const animalsCheckbox = this.page.locator('input[type="checkbox"][name*="animalsAccepted"]');
    if (await animalsCheckbox.isVisible()) {
      if (conditions.animalsAccepted) {
        await animalsCheckbox.check();
      } else {
        await animalsCheckbox.uncheck();
      }
    }

    // Handle smoking allowed
    const smokingCheckbox = this.page.locator('input[type="checkbox"][name*="smokingAllowed"]');
    if (await smokingCheckbox.isVisible()) {
      if (conditions.smokingAllowed) {
        await smokingCheckbox.check();
      } else {
        await smokingCheckbox.uncheck();
      }
    }

    // Add additional rules
    if (conditions.additionalRules.length > 0) {
      const rulesTextarea = this.page.locator('textarea[name*="additionalRules"], textarea[name*="rules"]');
      await rulesTextarea.fill(conditions.additionalRules.join('\n'));
    }
  }

  async submitCurrentStep() {
    // Try multiple selectors for submit button
    const submitSelectors = [
      'button[type="submit"]',
      'button:has-text("Sauvegarder")',
      'button:has-text("Suivant")',
      'button:has-text("Continuer")',
      'button:has-text("Étape suivante")',
      'button:has-text("Valider")',
      'button.btn-primary',
      'button.submit-btn',
      'input[type="submit"]',
      'a:has-text("Suivant")',
      'a:has-text("Continuer")'
    ];

    let clicked = false;
    for (const selector of submitSelectors) {
      try {
        const element = this.page.locator(selector).first();
        if (await element.count() > 0 && await element.isVisible()) {
          await element.click();
          clicked = true;
          break;
        }
      } catch { }
    }

    if (!clicked) {
      // On some steps like geolocation, there might be no explicit submit
      // Try to navigate by clicking any primary action button
      try {
        const primaryAction = this.page.locator('.btn-primary, .btn-success, [data-action="next"]').first();
        if (await primaryAction.count() > 0 && await primaryAction.isVisible()) {
          await primaryAction.click();
        }
      } catch { }
    }

    // Wait for navigation or at least network idle
    await Promise.race([
      this.page.waitForURL(/\/deposez-votre-annonce\//, { timeout: 5000 }).catch(() => { }),
      this.page.waitForLoadState('networkidle', { timeout: 5000 }).catch(() => { })
    ]);
  }

  async goToNextStep() {
    await this.submitCurrentStep();
  }

  async isOnStep(step: string): Promise<boolean> {
    const url = this.page.url();
    return url.includes(`/deposez-votre-annonce/${step}`);
  }

  async getSuccessMessage(): Promise<string | null> {
    const successAlert = this.page.locator('.alert-success, [role="alert"].success');
    if (await successAlert.isVisible()) {
      return await successAlert.textContent();
    }
    return null;
  }

  async getErrorMessage(): Promise<string | null> {
    const errorAlert = this.page.locator('.alert-danger, .alert-error, [role="alert"].error');
    if (await errorAlert.isVisible()) {
      return await errorAlert.textContent();
    }
    return null;
  }

  async publishRental() {
    const publishButton = this.page.locator('button:has-text("Publier"), button[data-action="publish"]');
    if (await publishButton.isVisible()) {
      await publishButton.click();
      await this.page.waitForResponse(response =>
        response.url().includes('/toggle-publish') && response.status() === 200
      );
    }
  }

  async previewRental() {
    const previewButton = this.page.locator('a:has-text("Prévisualiser"), button:has-text("Aperçu")');
    if (await previewButton.isVisible()) {
      await previewButton.click();
      await this.page.waitForLoadState('networkidle');
    }
  }

  // Helper methods
  private async setPeopleCount(count: number) {
    const plusButton = this.page.locator('[data-counter-target="plus"], button:has-text("+")').first();
    const minusButton = this.page.locator('[data-counter-target="minus"], button:has-text("-")').first();
    const input = this.page.locator('input[name*="peopleCount"]');

    if (await input.isVisible()) {
      await input.fill(count.toString());
    } else {
      // Use counter buttons
      for (let i = 0; i < count - 1; i++) {
        await plusButton.click();
      }
    }
  }

  private async setBedroomCount(count: number) {
    const input = this.page.locator('input[name*="bedroomCount"]');
    const plusButton = this.page.locator('[data-action*="bedroom"] button:has-text("+")');

    if (await input.isVisible()) {
      await input.fill(count.toString());
    } else if (await plusButton.isVisible()) {
      for (let i = 0; i < count - 1; i++) {
        await plusButton.click();
      }
    }
  }

  private async setBedCount(bedroomIndex: number, bedType: string, count: number) {
    const selector = `[data-bedroom-index="${bedroomIndex}"] input[name*="${bedType}"], .bedroom-${bedroomIndex} input[name*="${bedType}"]`;
    const input = this.page.locator(selector);

    if (await input.isVisible()) {
      await input.fill(count.toString());
    }
  }

  private async selectOption(fieldName: string, value: string) {
    // Try select dropdown first
    const select = this.page.locator(`select[name*="${fieldName}"]`);
    if (await select.isVisible()) {
      await select.selectOption({ label: value });
      return;
    }

    // Try radio buttons
    const radio = this.page.locator(`input[type="radio"][name*="${fieldName}"]`);
    if (await radio.first().isVisible()) {
      await this.page.click(`label:has-text("${value}")`);
      return;
    }

    // Try regular input
    const input = this.page.locator(`input[name*="${fieldName}"]`);
    if (await input.isVisible()) {
      await input.fill(value);
    }
  }
}

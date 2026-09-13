# Playwright E2E Tests

This directory contains end-to-end tests using [Playwright](https://playwright.dev/).

## Structure

- `specs/` - Test specifications
- `pages/` - Page Object Models
- `helpers/` - Test utilities and helpers
- `fixtures/` - Custom test fixtures

## Running Tests

### Prerequisites

1. Install dependencies:
   ```bash
   bun install
   ```

2. Install Playwright browsers:
   ```bash
   bun x playwright install
   ```

3. Set up test database:
   ```bash
   make test_playwright_setup
   ```

### Running Tests

Run all tests:
```bash
make test_playwright
```

Run tests in UI mode (recommended for development):
```bash
make test_playwright_ui
```

Run tests in debug mode:
```bash
make test_playwright_debug
```

View test report:
```bash
make test_playwright_report
```

### Writing Tests

1. **Use Page Object Models**: All page interactions should go through page objects in the `pages/` directory.

2. **Use Custom Fixtures**: Import test fixtures from `fixtures/test.ts` instead of `@playwright/test`:
   ```typescript
   import { test, expect } from '../fixtures/test';
   ```

3. **Follow Naming Conventions**: 
   - Test files: `*.spec.ts`
   - Page objects: `*.page.ts`
   - Helpers: `*.helper.ts`

4. **Use Domain Language**: Follow the project's ubiquitous language (e.g., "persona" instead of "user" for favorites).

### Example Test

```typescript
import { test, expect } from '../fixtures/test';

test.describe('Feature Name', () => {
  test.beforeEach(async ({ db }) => {
    await db.clearDatabase();
    await db.loadFixtures();
  });

  test('should do something', async ({ authPage, homePage, page }) => {
    // Arrange
    await authPage.login('user@example.com', 'password');
    
    // Act
    await homePage.goto();
    await page.click('button');
    
    // Assert
    await expect(page.locator('h1')).toContainText('Expected Text');
  });
});
```

### CI/CD

Tests run automatically on GitHub Actions for:
- Push to `main` or `develop` branches
- Pull requests to `main` or `develop` branches

Test reports and screenshots are uploaded as artifacts on test failures.

### Migrating from Panther

When migrating tests from Symfony Panther:

1. Convert PHP test methods to TypeScript test functions
2. Replace Panther's WebDriver calls with Playwright's API
3. Use Playwright's auto-waiting instead of explicit waits where possible
4. Leverage Playwright's better selectors (text content, role-based, etc.)

### Tips

1. **Debugging**: Use `page.pause()` to pause execution and debug in browser
2. **Screenshots**: Tests automatically capture screenshots on failure
3. **Selectors**: Prefer user-facing attributes (text, roles) over CSS classes
4. **Parallel Execution**: Tests run in parallel by default for faster execution
5. **Cross-browser**: Tests run on Chromium, Firefox, and WebKit by default
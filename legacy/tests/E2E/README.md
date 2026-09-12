# E2E Tests

This directory contains end-to-end tests using Symfony Panther for browser automation.

## Prerequisites

1. Chrome or Chromium browser installed
2. Symfony server running
3. Database configured for test environment
4. Built assets

## Setup

1. Start the test server on port 9080:
```bash
symfony server:stop
symfony server:start --port=9080 --no-tls -d
```

2. Build assets:
```bash
npm run build:dev
```

3. Set up the test database:
```bash
make test_e2e_setup
```

## Running E2E Tests

Run all E2E tests:
```bash
make test_e2e
```

Or run directly:
```bash
vendor/bin/phpunit -c phpunit-e2e.xml
```

## Configuration

The E2E tests are configured to run in headless Chrome mode with the following options:
- `--headless=new`: Use new headless mode
- `--disable-gpu`: Disable GPU hardware acceleration
- `--no-sandbox`: Required for some environments
- `--disable-dev-shm-usage`: Prevent shared memory issues
- `--window-size=1920,1080`: Set viewport size

## Screenshot Functionality

### Automatic Screenshots on Failure
When a test fails, a screenshot is automatically taken and saved to `./var/error-screenshots/`. The screenshot includes:
- PNG screenshot of the browser state
- HTML source code of the page

Screenshot files are named: `{TestClass}_{testMethod}_{timestamp}.png`

### Manual Debug Screenshots
You can also take screenshots manually during test development:

```php
// Take a screenshot with auto-generated name
$this->takeDebugScreenshot();

// Take a screenshot with custom suffix
$this->takeDebugScreenshot('after_login');
```

### Viewing Screenshots
Screenshots are saved in `./var/error-screenshots/` and are ignored by git. To view them:

```bash
ls -la var/error-screenshots/
open var/error-screenshots/  # macOS
```

## Troubleshooting

### "No such window" errors
This is a known compatibility issue with Chrome 138+ and Panther on macOS. Potential solutions:
1. Downgrade Chrome to version 137 or earlier
2. Use a different browser (Firefox)
3. Run tests in a Docker container with a compatible Chrome version
4. Wait for Panther update that fixes the compatibility issue

### Tests can't find elements
Make sure:
1. The server is running on port 9080
2. Assets are built
3. Wait for elements before asserting: `$this->waitForElement('selector')`

### Debugging
To run tests with visible browser (non-headless):
1. Comment out `'--headless=new'` in BaseE2ETrait.php
2. Set `PANTHER_NO_HEADLESS=1` environment variable

## Known Issues

- Chrome 138+ has compatibility issues with Panther on macOS causing "no such window" errors
- Unit tests work correctly with `make test`
- E2E tests require Chrome compatibility fixes

## Test Structure

- `BaseE2ETrait.php`: Shared setup and helper methods
- `LoginTest.php`: Tests for login functionality
- `RegisterTest.php`: Tests for user registration
- `RequestResetPasswordTest.php`: Tests for password reset
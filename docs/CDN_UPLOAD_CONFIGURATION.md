# CDN Upload Configuration

This document explains how media uploads work in the application and how to configure CDN uploads for different environments.

## Overview

The application uses VichUploader with Flysystem to handle file uploads. In production, files are uploaded to BunnyCDN, while in development, files can be stored locally to avoid CDN costs and simplify development.

## Configuration

### Environment Variables

Add the following to your `.env.local` file:

```env
# BunnyCDN Configuration
BUNNYCDN_BUCKET=your-storage-zone-name
BUNNYCDN_API_KEY=your-api-key
BUNNYCDN_REGION=de  # or your region (de, ny, la, sg, etc.)
CDN_URL=https://your-pullzone.b-cdn.net
```

### Development Mode (Local Storage)

By default, the development environment is configured to use local storage instead of BunnyCDN. Files are stored in `public/uploads/`.

To use BunnyCDN in development:
1. Comment out or remove the entire content of `config/packages/dev/flysystem.yaml`
2. Ensure your `.env.local` has valid BunnyCDN credentials

### Production Mode (BunnyCDN)

In production, the application automatically uses BunnyCDN. Ensure your environment has the proper credentials set.

## Testing CDN Connection

Use the provided command to test your CDN configuration:

```bash
# Test connection only
php bin/console app:test-cdn-upload --check-connection

# Test connection and upload a file
php bin/console app:test-cdn-upload

# Test with a specific file
php bin/console app:test-cdn-upload --test-file=/path/to/test.jpg
```

## Troubleshooting

### Fixtures Not Uploading to CDN

The `RentalFixtures` now includes detailed logging. Check the logs when running fixtures:

```bash
php bin/console doctrine:fixtures:load --group=rental -vvv
```

Common issues:
1. **Invalid credentials**: Check your `.env.local` file
2. **Network issues**: Ensure your server can reach BunnyCDN API
3. **Wrong region**: Verify the BUNNYCDN_REGION matches your storage zone

### Error Messages

- **"Fixture image not found"**: The fixture images are missing from `src/Infrastructure/Symfony/DataFixtures/fixtures/rental/`
- **"Failed to upload image to CDN"**: Check the logs for specific error details
- **"401 Unauthorized"**: Invalid API key
- **"404 Not Found"**: Wrong bucket name or region

## Architecture

### Components

1. **VichUploader**: Handles file upload lifecycle
2. **Flysystem**: Provides storage abstraction
3. **BunnyCDNAdapter**: Custom adapter for BunnyCDN storage
4. **MediaService**: Generates correct URLs for assets (CDN or local)

### Upload Flow

1. User uploads file via form or fixtures create Media entities
2. VichUploader processes the upload
3. Flysystem routes to appropriate storage (local or BunnyCDN)
4. MediaService generates the correct URL for display

## Local Development Benefits

Using local storage in development:
- No CDN costs during development
- Faster uploads (no network latency)
- Works offline
- Easier debugging

## Switching Between Environments

The system automatically detects the environment and uses the appropriate storage:
- `dev`: Local storage (unless overridden)
- `test`: Can use either (configure as needed)
- `prod`: Always uses BunnyCDN
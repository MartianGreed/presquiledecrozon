# CDN Configuration Guide

This guide explains how to configure and troubleshoot CDN uploads for the Presqu'île de Crozon platform.

## Overview

The platform uses BunnyCDN for storing and serving media files. In development, you can choose between:
- **Local storage** (default): Files are stored in `public/uploads/`
- **CDN storage**: Files are uploaded to BunnyCDN

## Local Development (Default)

By default, the development environment uses local file storage to avoid CDN costs and allow offline development.

- Files are stored in: `public/uploads/`
- No CDN credentials required
- Works offline
- Zero cost

## CDN Configuration

To use BunnyCDN in development or production, configure these environment variables in `.env.local`:

```bash
# BunnyCDN Storage Zone settings
BUNNYCDN_HOSTNAME=storage.bunnycdn.com
BUNNYCDN_API_KEY=your-storage-zone-api-key
BUNNYCDN_STORAGE_ZONE=your-storage-zone-name

# BunnyCDN Pull Zone URL
BUNNYCDN_PULL_ZONE=https://your-pull-zone.b-cdn.net
```

### Getting BunnyCDN Credentials

1. Log in to [BunnyCDN](https://panel.bunny.net)
2. Go to "Storage Zones" and find your zone
3. Click on the zone to get:
   - Storage Zone name
   - API Key (under "FTP & API Access")
4. Go to "Pull Zones" to get your CDN URL

## Testing CDN Configuration

Use the test command to verify your setup:

```bash
# Test connection only
php bin/console app:test-cdn-upload

# Test with actual upload
php bin/console app:test-cdn-upload --upload
```

## Switching Between Local and CDN Storage

### Use Local Storage (Development)
This is the default. The file `config/packages/dev/flysystem.yaml` configures local storage.

### Use CDN Storage (Development)
1. Add CDN credentials to `.env.local`
2. Delete or rename `config/packages/dev/flysystem.yaml`
3. Run the test command to verify

## Running Fixtures

### With Local Storage
```bash
php bin/console doctrine:fixtures:load --group=rental
```

### With CDN Storage
```bash
# Run with verbose output to see upload progress
php bin/console doctrine:fixtures:load --group=rental -vvv
```

## Troubleshooting

### Images Not Uploading

1. **Check fixture images exist:**
   ```bash
   ls -la src/Infrastructure/Symfony/DataFixtures/fixtures/rental/
   ```

2. **Check environment variables:**
   ```bash
   php bin/console app:test-cdn-upload
   ```

3. **Check logs:**
   ```bash
   tail -f var/log/dev.log
   ```

### Common Issues

- **"BUNNYCDN_API_KEY is not set"**: Add credentials to `.env.local`
- **"Failed to connect to CDN"**: Check API key and network connection
- **"Fixture image not found"**: Ensure fixture images exist in the correct directory
- **Images upload but don't display**: Check BUNNYCDN_PULL_ZONE is correct

### Debug Mode

The RentalFixtures now includes detailed logging. Run with `-vvv` to see:
- Upload attempts
- Success/failure status
- Error messages
- File paths and sizes

## Production Configuration

In production, CDN credentials should be set through environment variables in your deployment platform (AWS Lambda, etc.). Never commit credentials to version control.
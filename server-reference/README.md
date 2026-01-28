# SocietyPress Update Server - Reference Implementation

This is a reference implementation for the SocietyPress update server that should be deployed to **stricklindevelopment.com**.

## Overview

The update server handles:
1. **Update checks** - Tells WordPress if a new version is available
2. **Plugin information** - Provides details for the "View details" popup
3. **Authenticated downloads** - Serves plugin ZIP files to licensed customers

## API Endpoints

All endpoints should be accessible at:
```
https://stricklindevelopment.com/api/v1/plugins/societypress/
```

### POST /update-check

Checks if an update is available.

**Request Body:**
```json
{
  "plugin_slug": "societypress",
  "current_version": "1.0.0",
  "license_key": "XXXX-XXXX-XXXX-XXXX",
  "site_url": "https://example.com",
  "wp_version": "6.4",
  "php_version": "8.2"
}
```

**Response (200 OK):**
```json
{
  "version": "1.1.0",
  "download_url": "https://stricklindevelopment.com/api/v1/plugins/societypress/download?license=XXXX&site=example.com&token=abc123",
  "homepage": "https://stricklindevelopment.com/studiopress/societypress",
  "requires": "6.0",
  "requires_php": "8.0",
  "tested": "6.4",
  "icons": {
    "1x": "https://stricklindevelopment.com/assets/societypress-icon-128x128.png",
    "2x": "https://stricklindevelopment.com/assets/societypress-icon-256x256.png"
  },
  "banners": {
    "low": "https://stricklindevelopment.com/assets/societypress-banner-772x250.png",
    "high": "https://stricklindevelopment.com/assets/societypress-banner-1544x500.png"
  }
}
```

### POST /info

Provides detailed plugin information for the "View details" popup.

**Request Body:**
```json
{
  "plugin_slug": "societypress",
  "license_key": "XXXX-XXXX-XXXX-XXXX",
  "site_url": "https://example.com"
}
```

**Response (200 OK):**
```json
{
  "name": "SocietyPress",
  "slug": "societypress",
  "version": "1.1.0",
  "author": "<a href='https://stricklindevelopment.com/studiopress/'>Charles Stricklin</a>",
  "homepage": "https://stricklindevelopment.com/studiopress/societypress",
  "requires": "6.0",
  "requires_php": "8.0",
  "tested": "6.4",
  "last_updated": "2026-01-25 10:00:00",
  "sections": {
    "description": "<p>Membership management for genealogical and historical societies...</p>",
    "changelog": "<h4>1.1.0</h4><ul><li>Added member portal</li><li>Fixed CSV import bug</li></ul>",
    "installation": "<ol><li>Upload to plugins directory</li><li>Activate plugin</li></ol>",
    "faq": "<h4>Can I import from Wild Apricot?</h4><p>Yes, CSV import supports Wild Apricot format.</p>"
  },
  "download_link": "https://stricklindevelopment.com/api/v1/plugins/societypress/download?license=XXXX&site=example.com&token=abc123",
  "icons": {
    "1x": "https://stricklindevelopment.com/assets/societypress-icon-128x128.png",
    "2x": "https://stricklindevelopment.com/assets/societypress-icon-256x256.png"
  },
  "banners": {
    "low": "https://stricklindevelopment.com/assets/societypress-banner-772x250.png",
    "high": "https://stricklindevelopment.com/assets/societypress-banner-1544x500.png"
  }
}
```

### GET /download

Downloads the plugin ZIP file (authenticated).

**Query Parameters:**
- `license` - License key
- `site` - Site URL (for validation)
- `token` - Temporary download token (generated during update-check)

**Response:**
- **200 OK** - Returns the plugin ZIP file
- **403 Forbidden** - Invalid license or token
- **404 Not Found** - File not found

**Headers:**
```
Content-Type: application/zip
Content-Disposition: attachment; filename=societypress-1.1.0.zip
```

## File Structure

```
/path/to/update-server/
├── api/
│   └── v1/
│       └── plugins/
│           └── societypress/
│               ├── update-check.php    # Update check endpoint
│               ├── info.php            # Plugin info endpoint
│               └── download.php        # Download endpoint
├── versions/
│   ├── societypress-1.0.0.zip
│   ├── societypress-1.1.0.zip
│   └── latest.json                     # Current version metadata
├── assets/
│   ├── societypress-icon-128x128.png
│   ├── societypress-icon-256x256.png
│   ├── societypress-banner-772x250.png
│   └── societypress-banner-1544x500.png
└── includes/
    ├── db.php                          # Database connection
    └── license-validator.php           # License validation logic
```

## Security Considerations

1. **License Validation** - Always validate license key + site URL combo
2. **Rate Limiting** - Limit update checks per IP/license
3. **Download Tokens** - Generate short-lived tokens (15-30 min expiry)
4. **HTTPS Only** - Never serve updates over HTTP
5. **File Permissions** - ZIP files should not be publicly accessible

## Database Tables

### licenses
```sql
CREATE TABLE licenses (
  id INT PRIMARY KEY AUTO_INCREMENT,
  license_key VARCHAR(255) NOT NULL UNIQUE,
  email VARCHAR(255) NOT NULL,
  license_type ENUM('site', 'multi', 'lifetime') NOT NULL,
  status ENUM('active', 'expired', 'suspended') NOT NULL,
  sites_allowed INT DEFAULT 1,
  created_at DATETIME NOT NULL,
  expires_at DATETIME,
  INDEX(license_key),
  INDEX(status)
);
```

### license_activations
```sql
CREATE TABLE license_activations (
  id INT PRIMARY KEY AUTO_INCREMENT,
  license_id INT NOT NULL,
  site_url VARCHAR(255) NOT NULL,
  activated_at DATETIME NOT NULL,
  last_check DATETIME,
  FOREIGN KEY (license_id) REFERENCES licenses(id),
  UNIQUE KEY unique_activation (license_id, site_url),
  INDEX(site_url)
);
```

### download_tokens
```sql
CREATE TABLE download_tokens (
  id INT PRIMARY KEY AUTO_INCREMENT,
  token VARCHAR(64) NOT NULL UNIQUE,
  license_key VARCHAR(255) NOT NULL,
  site_url VARCHAR(255) NOT NULL,
  version VARCHAR(20) NOT NULL,
  created_at DATETIME NOT NULL,
  expires_at DATETIME NOT NULL,
  used_at DATETIME,
  INDEX(token),
  INDEX(expires_at)
);
```

## Deployment

1. Upload files to stricklindevelopment.com
2. Configure database connection in `includes/db.php`
3. Set up SSL certificate (required)
4. Configure URL rewrites (Apache/Nginx)
5. Set appropriate file permissions
6. Test with curl:
   ```bash
   curl -X POST https://stricklindevelopment.com/api/v1/plugins/societypress/update-check \
     -H "Content-Type: application/json" \
     -d '{"plugin_slug":"societypress","current_version":"1.0.0","license_key":"test","site_url":"localhost"}'
   ```

## Testing

Use the included `test-endpoints.php` script to verify all endpoints are working correctly.

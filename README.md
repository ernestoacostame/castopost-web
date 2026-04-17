# CastoPOST

A self-hosted web panel for publishing podcast episodes to [Castopod](https://castopod.org/) via its REST API. Built with PHP, no framework dependencies, designed to run on any standard LAMP/LEMP server.

![CastoPOST panel screenshot](https://via.placeholder.com/800x400/0f0f10/c9b8ff?text=CastoPOST)

---

## Features

### Publishing
- Publish episodes directly to Castopod from any browser or mobile device
- Upload audio files (MP3, M4A, WAV, FLAC, OGG, OPUS) or provide a remote URL
- **Browser-based audio recorder** - record directly from your microphone, no app needed
- Automatic conversion of browser recordings from WebM/Opus to MP3 via FFmpeg
- Upload episode cover image per episode
- Set episode number, season, type (full/trailer/bonus), explicit flag, publication date
- Schedule future publication
- Auto-generate URL slug from title
- Auto-detect next episode number based on previously published episodes

### Drafts & Templates
- **Local drafts** - save form state to the server mid-editing, resume from any device
- **Description templates** - create reusable templates with Markdown support, apply with one click
- **Live preview** - see a real-time preview of the episode (title, metadata, description) as you type

### Dashboard & Management
- Dashboard with podcast cover, description, feed URL, and episode stats
- Full **episode list** with number, date, duration, and inline audio player
- **Castopod drafts** section - view and publish episodes sitting as drafts in Castopod
- **Local drafts** section - manage saved form drafts
- **Multi-podcast support** - manage multiple podcasts, stored persistently on the server (not per-browser)
- Podcast tabs with distinct background colors to avoid confusion when switching
- Temporary file manager - view and clean up audio files in /tmp from the dashboard

### Interface
- Responsive design with hamburger menu on mobile (pure CSS, no JavaScript dependency)
- Floating "Publish" button on mobile for quick access
- 5 color themes (Zinc, Stone, Sage, Iris, Paper) - saved per-browser
- Fonts: Noto Serif, Ubuntu, Noto Sans Mono (Google Fonts, open license)
- Private access via single password with server-side session (8-hour expiry)

### API Integration
- Uses Castopod REST API v1 with HTTP Basic Auth
- Resolves podcast handles to numeric IDs automatically
- Two-step publish flow: create draft -> publish (matches Castopod's internal model)
- Paginated episode fetching - handles podcasts with 800+ episodes
- Graceful error messages pointing to the exact API call and response

---

## Requirements

- **PHP** 8.0 or higher
- PHP extensions: `curl`, `json`, `fileinfo`
- **FFmpeg** (for converting browser recordings to MP3)
- **Nginx** or Apache web server with PHP-FPM
- **Castopod** 1.x with REST API enabled
- Write permissions on `tmp/`, `podcasts.json`, `local_drafts.json`, `templates.json`

---

## Installation

### 1. Clone or download

```bash
git clone https://github.com/yourusername/castopost.git /var/www/html/mysite/studio
# or download and extract the ZIP
```

### 2. Set file permissions

```bash
cd /var/www/html/mysite/studio
chmod 700 tmp/
chmod 664 podcasts.json local_drafts.json templates.json
```

### 3. Enable Castopod REST API

Add the following lines to your Castopod `.env` file (usually at `/var/www/html/castopod/.env`):

```ini
restapi.enabled=true
restapi.basicAuth=true
restapi.basicAuthUsername="your_api_username"
restapi.basicAuthPassword="your_api_password"
```

Then restart PHP-FPM:

```bash
systemctl restart php8.3-fpm
```

> **Note:** These API credentials are **independent** of your Castopod admin panel username/password. Choose any username and password you want.

### 4. Find your Castopod user ID

```bash
mysql -u YOUR_DB_USER -p YOUR_DB_NAME -e "SELECT id, username FROM cp_users;"
```

Note the numeric `id` of your user (usually `1`).

### 5. Find your podcast handle

Your podcast handle is the slug used in its public URL:
- `https://yourcastopod.com/@my-podcast` → handle is `my-podcast`

Or go to: **Castopod Admin → Your Podcast → Settings → Handle/Slug**

> Do NOT use the numeric ID from the admin URL (`/cp-admin/podcasts/13`) — use the slug.

### 6. Configure the application

Copy and edit the config file:

```bash
cp config.php config.php.bak
nano config.php
```

```php
// Password to access this panel (change this!)
define('APP_PASSWORD', 'your_secure_password');

// Your Castopod instance URL (no trailing slash)
define('CASTOPOD_URL', 'https://your-castopod.com');

// REST API credentials (from your Castopod .env)
define('CASTOPOD_API_USER',     'your_api_username');
define('CASTOPOD_API_PASSWORD', 'your_api_password');

// Default podcast handle (slug)
define('CASTOPOD_PODCAST_HANDLE', 'my-podcast');

// Your numeric user ID in Castopod
define('CASTOPOD_USER_ID', 1);

// Timezone: https://www.php.net/manual/en/timezones.php
date_default_timezone_set('America/New_York'); // Change to your timezone
```

### 7. Install FFmpeg

FFmpeg is required to convert browser recordings (WebM/Opus) to MP3:

```bash
# Ubuntu/Debian
sudo apt update && sudo apt install ffmpeg

# Verify installation
ffmpeg -version
```

### 8. Configure Nginx

See the included `nginx.conf.example` for a full configuration. Key blocks for the studio panel:

```nginx
# Block sensitive PHP files
location ~ ^/studio/(config|auth|castopod|drafts_store|templates_store|podcasts_store)\.php$ {
    deny all;
    return 404;
}

# Block data files
location ~ ^/studio/.*\.(json)$ {
    deny all;
    return 404;
}

# Block tmp directory
location ^~ /studio/tmp/ {
    deny all;
    return 404;
}

# PHP processing for studio
location ^~ /studio/ {
    index index.php;
    try_files $uri $uri/ /studio/index.php?$query_string;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 600;
        fastcgi_send_timeout 600;
    }
}

# Allow large audio uploads
client_max_body_size 520M;
```

### 9. Access the panel

Navigate to `https://yourdomain.com/studio/` and log in with the password you set in `config.php`.

---

## File Structure

```
studio/
├── index.php              # Main controller / router
├── config.php             # Configuration (edit this)
├── auth.php               # Session authentication
├── castopod.php           # Castopod API client
├── podcasts_store.php     # Multi-podcast persistence (JSON)
├── drafts_store.php       # Local episode drafts (JSON)
├── templates_store.php    # Description templates (JSON)
├── podcasts.json          # Stored podcast list (auto-created)
├── local_drafts.json      # Local drafts storage (auto-created)
├── templates.json         # Description templates (auto-created)
├── tmp/                   # Temporary audio files (needs write permission)
├── templates/             # PHP view templates
│   ├── layout.php         # Main HTML layout, nav, styles
│   ├── login.php
│   ├── dashboard.php
│   ├── publish.php        # Episode publish form
│   ├── episodes.php       # Published episodes list
│   ├── drafts.php         # Castopod drafts
│   ├── local_drafts.php   # Local drafts list
│   ├── podcasts_page.php  # Podcast management
│   └── templates_page.php # Description templates
├── nginx.conf.example     # Nginx configuration example
└── README.md
```

---

## Castopod API Limitations

The Castopod REST API v1 supports:
- `GET /api/rest/v1/podcasts` - list podcasts
- `GET /api/rest/v1/podcasts/{id}` - podcast details
- `GET /api/rest/v1/episodes` - list episodes
- `POST /api/rest/v1/episodes` - create episode (draft)
- `POST /api/rest/v1/episodes/{id}/publish` - publish episode

**Not supported by the API:**
- Editing published episodes
- Deleting episodes
- Unpublishing episodes
- Analytics/download stats

For operations not supported by the API, use the Castopod admin panel directly.

---

## Troubleshooting

**404 on API calls**
- Confirm `restapi.enabled=true` is in your Castopod `.env`
- Restart PHP-FPM after editing `.env`
- Verify the API URL by running:
  ```bash
  curl -u "api_user:api_password" https://your-castopod.com/api/rest/v1/podcasts/
  ```

**"User not found" when publishing**
- Check `CASTOPOD_USER_ID` matches your actual user ID in the `cp_users` table
- The publish endpoint uses `created_by` (form-encoded), not JSON

**Audio conversion fails**
- Verify FFmpeg is installed: `which ffmpeg`
- Check the `tmp/` directory is writable: `ls -la tmp/`

**PHP Parse errors on load**
- Check for smart quotes or em-dashes in PHP files — use only ASCII in PHP strings
- Verify PHP version: `php --version` (requires 8.0+)

**Enable debug mode** (temporarily, for diagnosis):
```php
define('CASTOPOD_DEBUG', true);
```
This logs API calls to your server's error log.

---

## Security Recommendations

- Use HTTPS (required for microphone access in browsers)
- Add HTTP Basic Auth at the Nginx level as a second authentication layer
- Restrict access by IP if your IP is static
- Keep `config.php` permissions at `644` or tighter — it is blocked from direct web access via Nginx config
- Regularly clear the `tmp/` directory (available from the dashboard)
- The `.json` data files are blocked from direct web access via Nginx config

---

## License

MIT License. See LICENSE file for details.

---

## Contributing

Pull requests welcome. Please test against Castopod 1.x and ensure no personal data or credentials are included in commits.

---

## Docker

CastoPOST can be run as a Docker container. The image bundles PHP 8.3, Nginx, and FFmpeg.

### Quick start (no HTTPS)

```bash
docker run -d \
  --name castopost \
  -p 8080:8080 \
  -v castopost_data:/data \
  -e APP_PASSWORD="your_secure_password" \
  -e CASTOPOD_URL="https://your-castopod.com" \
  -e CASTOPOD_API_USER="your_api_user" \
  -e CASTOPOD_API_PASSWORD="your_api_password" \
  -e CASTOPOD_PODCAST_HANDLE="my-podcast" \
  -e CASTOPOD_USER_ID="1" \
  castopost:latest
```

Then open `http://localhost:8080`.

### With docker-compose (recommended)

```bash
# Copy and edit the compose file
cp docker-compose.yml docker-compose.override.yml
nano docker-compose.override.yml  # set your environment variables

# Build and start
docker compose up -d

# View logs
docker compose logs -f
```

### With HTTPS reverse proxy

Use `docker-compose.proxy.yml` to add an Nginx reverse proxy with SSL:

```bash
# Edit docker/proxy.conf - replace YOUR_DOMAIN
# Make sure Let's Encrypt certs exist at /etc/letsencrypt

docker compose -f docker-compose.proxy.yml up -d
```

### Environment variables

| Variable | Required | Default | Description |
|---|---|---|---|
| `APP_PASSWORD` | Yes | `change_me_now` | Login password for the panel |
| `CASTOPOD_URL` | Yes | — | Your Castopod instance URL |
| `CASTOPOD_API_USER` | Yes | — | REST API username (from `.env`) |
| `CASTOPOD_API_PASSWORD` | Yes | — | REST API password (from `.env`) |
| `CASTOPOD_PODCAST_HANDLE` | Yes | `my-podcast` | Default podcast slug |
| `CASTOPOD_USER_ID` | Yes | `1` | Your numeric user ID in Castopod |
| `APP_TIMEZONE` | No | `UTC` | PHP timezone string |
| `CASTOPOD_DEBUG` | No | `false` | Enable API debug logging |
| `MAX_AUDIO_SIZE` | No | `536870912` | Max audio upload size in bytes (512 MB) |

### Volume: /data

All persistent data is stored in the `/data` volume:

```
/data/
├── config.php          # Auto-generated from env vars (editable)
├── podcasts.json       # Saved podcast list
├── local_drafts.json   # Episode drafts
├── templates.json      # Description templates
└── tmp/                # Temporary audio files
```

The `config.php` inside `/data` is auto-generated on first run from environment variables. You can also edit it directly inside the volume for advanced configuration without rebuilding the image.

### Build from source

```bash
git clone https://github.com/yourusername/castopost.git
cd castopost
docker build -t castopost:latest .
```

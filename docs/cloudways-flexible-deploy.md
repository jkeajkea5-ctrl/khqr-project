# Cloudways Flexible Deployment Guide

This project is a Laravel 12 application with:

- PHP `^8.2`
- MySQL
- Vite / Tailwind frontend build
- Local public file uploads
- Laravel scheduler for `orders:sync`
- Bakong KHQR and Telegram environment variables

## Recommended deployment approach

Use a **Laravel application** on **Cloudways Flexible** and deploy by **Git**.

If you are not using Git yet, initialize a repository locally and push it to GitHub or GitLab first.

## 1. Prepare the project locally

Before pushing code:

- Make sure `.env` is **not** committed
- Make sure `vendor/`, `node_modules/`, and `public/build/` are **not** committed
- Commit only source code and `composer.lock` / `package-lock.json`

This project already ignores the important local files in `.gitignore`.

## 2. Create the app on Cloudways

In Cloudways Flexible:

1. Launch a new server
2. Add a new application
3. Choose **Laravel**
4. After setup finishes, open the new application

## 3. Deploy code from Git

In **Application Management -> Deployment via Git**:

1. Generate SSH keys
2. Add the Cloudways public key to your Git provider as a deploy key
3. Paste your repository SSH URL
4. Authenticate
5. Select your branch
6. Deploy into the default `public_html/` path

## 4. SSH into the application

Use the Cloudways SSH terminal or your SSH client, then go to the app folder:

```bash
cd /home/master/applications/<APP_FOLDER>/public_html
```

Replace `<APP_FOLDER>` with your Cloudways application folder name.

## 5. Install backend dependencies

```bash
composer install --no-dev --optimize-autoloader
```

## 6. Create production environment file

In the Cloudways application `.env`, set at least:

```env
APP_NAME=KHQR Bakong
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_cloudways_db
DB_USERNAME=your_cloudways_db_user
DB_PASSWORD=your_cloudways_db_password

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
FILESYSTEM_DISK=public

BAKONG_TOKEN=your_token
BAKONG_API_URL=https://api-bakong.nbc.gov.kh
BAKONG_ACCOUNT_ID=your_account
BAKONG_MERCHANT_NAME=your_name
BAKONG_MERCHANT_CITY=Phnom Penh
BAKONG_CURRENCY=USD
BAKONG_STORE_LABEL=KHQR Shop
BAKONG_TERMINAL_LABEL=WEB1
BAKONG_PURPOSE=Order payment
BAKONG_QR_EXPIRY_SECONDS=900

TELEGRAM_BOT_TOKEN=your_bot_token
TELEGRAM_CHAT_ID=your_chat_id
```

Important:

- Use `FILESYSTEM_DISK=public` in production for this project
- Keep `APP_DEBUG=false`

## 7. Run Laravel setup commands

```bash
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

## 8. Build frontend assets

Because `public/build` is ignored in Git, build assets on the server:

```bash
npm ci
npm run build
```

Cloudways supports local Node module usage in the application directory.

## 9. Configure the scheduler

This app schedules `orders:sync` every minute, so add a cron job in Cloudways:

```cron
* * * * * php /home/master/applications/<APP_FOLDER>/public_html/artisan schedule:run > /dev/null 2>&1
```

## 10. Domain and SSL

In Cloudways:

1. Set your primary domain
2. Point DNS to the server IP
3. Install a Let's Encrypt SSL certificate

After that, make sure:

- `APP_URL` matches the real `https://` domain
- the site loads over HTTPS

## 11. Verify the app

Test these flows:

- home page
- product page
- user register/login
- cart
- checkout page
- payment verification
- invoice page
- admin login
- image upload in admin

## 12. Important notes for this project

- Uploaded images are stored on the server's local disk using Laravel's `public` disk
- That is fine for a single Cloudways server
- If you later scale to multiple app servers, move uploads to S3-compatible storage
- The app currently uses scheduler-based payment syncing, not a dedicated queue worker

## 13. Optional next improvements

- Add a Git repository if the project is not under Git yet
- Add a deployment script to automate Composer, migrations, and Vite build
- Move user uploads to object storage for easier scaling
- Move cache / sessions to Redis later if traffic grows

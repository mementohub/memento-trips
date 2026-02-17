---
description: Deploy Memento Trips on Ploi.io after server is provisioned
---

# Ploi.io Deployment — Memento Trips

## Prerequisites (server already provisioned in Ploi)

- **PHP 8.4** 
- **MySQL 8.4**
- **Node.js 22+** (install via Ploi → Server → Tools → Node.js)
- **Composer 2.x** (pre-installed by Ploi)

---

## Step 1 — Create Site in Ploi

1. Go to your server in Ploi → **Sites** → **Add Site**
2. Set the **Domain** (e.g., `mementotrips.com`)
3. Set **Web Directory** to `/public`
4. Choose **PHP 8.4**
5. Click **Create Site**

---

## Step 2 — Connect Git Repository

1. Go to the site → **Repository**
2. Connect to: `triskelionmarket-cmyk/memento-trips`
3. Branch: `main`
4. Check **Install Composer Dependencies**
5. Click **Install Repository**

---

## Step 3 — Create MySQL Database

1. Go to Server → **Databases** → **Add Database**
2. Database name: `memento_trips`
3. Create a database user with a strong password
4. Note the credentials for `.env`

---

## Step 4 — Import Database Dump

SSH into the server (Ploi → Server → SSH Keys, or use the Terminal feature):

```bash
cd /home/ploi/mementotrips.com
mysql -u DB_USER -p memento_trips < reaktive_travel.sql
```

Replace `DB_USER` with your database username.

---

## Step 5 — Configure Environment

Go to site → **Environment** (`.env` tab) and set:

```env
APP_NAME="Memento Trips"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://mementotrips.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=memento_trips
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

CACHE_STORE=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@mementotrips.com
MAIL_FROM_NAME="Memento Trips"
```

Then generate the app key via SSH:

```bash
cd /home/ploi/mementotrips.com
php artisan key:generate
```

---

## Step 6 — Set Deploy Script

Go to site → **Deploy Script** and paste:

```bash
cd /home/ploi/mementotrips.com

git pull origin main

composer install --no-dev --optimize-autoloader

npm ci
npm run build

php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force

echo "Deploy complete 🚀"
```

---

## Step 7 — SSL Certificate

1. Go to site → **SSL**
2. Click **Request Certificate** (Let's Encrypt)
3. Enable **Force HTTPS**

---

## Step 8 — First Deploy

Click **Deploy Now** in Ploi. Watch the deploy log for any errors.

---

## Step 9 — Verify

1. Visit `https://mementotrips.com` — front page should load
2. Visit `https://mementotrips.com/admin` — admin login page
3. Check that images load (storage:link working)
4. Test a booking flow end-to-end

---

## Optional — Queue Worker (if using async queues later)

If you switch `QUEUE_CONNECTION` from `sync` to `database` or `redis`:

1. Go to site → **Daemons** → Add:
   - Command: `php artisan queue:work --sleep=3 --tries=3 --max-time=3600`
   - Directory: `/home/ploi/mementotrips.com`
2. Start the daemon

---

## Optional — Scheduler (for scheduled tasks)

Go to Server → **Cron Jobs** → Add:

```
* * * * * cd /home/ploi/mementotrips.com && php artisan schedule:run >> /dev/null 2>&1
```

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| 500 Error | Check `storage/logs/laravel.log`, ensure `APP_DEBUG=true` temporarily |
| CSS/JS not loading | Run `npm run build` again, check `public/build/` exists |
| Images not showing | Run `php artisan storage:link` |
| Permission errors | `chmod -R 775 storage bootstrap/cache` |
| Migration fails | Already imported the SQL dump? Skip migrations or run selectively |

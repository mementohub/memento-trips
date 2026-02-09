# Deployment Guide — Ploi.io

## Prerequisites

- Ploi.io account with a connected server (Ubuntu 22.04+)
- PHP 8.1+ with extensions: `mbstring`, `xml`, `curl`, `mysql`, `gd`, `zip`, `bcmath`
- MySQL 5.7+ or MariaDB 10.3+
- Composer 2.x
- Git repository (GitHub, GitLab, or Bitbucket)

## 1. Create the Site

1. In **Ploi → Sites**, click **Add Site**
2. Enter your domain (e.g. `mementotrips.com`)
3. Set **Web directory** to `/public`
4. Select **PHP 8.1** (or 8.2/8.3)
5. Click **Create**

## 2. Connect Repository

1. Go to **Site → Repository**
2. Connect your Git provider
3. Select the repository and branch (`main`)
4. Enable **Install Composer dependencies** option

## 3. Environment Variables

1. Go to **Site → Environment**
2. Edit the `.env` file with production values:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_KEY=  # Will be generated in deploy script

DB_HOST=127.0.0.1
DB_DATABASE=memento_trips_prod
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=hello@your-domain.com
```

## 4. Deploy Script

Set the following deploy script in **Site → Deploy Script**:

```bash
cd /home/ploi/your-domain.com

git pull origin main

composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link

# Restart queue workers if using queues
# php artisan queue:restart

echo "Deployed successfully"
```

## 5. SSL Certificate

1. Go to **Site → SSL**
2. Click **Request Let's Encrypt Certificate**
3. Enable **Force HTTPS**

## 6. Database Setup (First Deploy)

For the initial deployment, import the SQL dump:

```bash
# SSH into the server
ssh ploi@your-server-ip

# Import database
mysql -u your_db_user -p memento_trips_prod < /path/to/dump.sql

# Generate app key if not set
cd /home/ploi/your-domain.com
php artisan key:generate --force
```

## 7. File Permissions

Ploi handles permissions automatically, but verify:

```bash
chmod -R 775 storage bootstrap/cache
chown -R ploi:ploi storage bootstrap/cache
```

## 8. Cron Job (Scheduled Tasks)

In **Ploi → Server → Cron Jobs**, add:

```
* * * * * cd /home/ploi/your-domain.com && php artisan schedule:run >> /dev/null 2>&1
```

## 9. Post-Deploy Checklist

- [ ] Verify homepage loads correctly
- [ ] Test admin login at `/admin/login`
- [ ] Test agency registration and login
- [ ] Test user registration and login
- [ ] Verify payment gateway settings in admin panel
- [ ] Test a booking flow end-to-end
- [ ] Verify email sending (password reset, booking confirmation)
- [ ] Check SSL certificate is active
- [ ] Verify storage symlink works (images load)

## Troubleshooting

| Issue | Fix |
|-------|-----|
| 500 error | Check `storage/logs/laravel.log`, ensure `.env` is correct |
| Images not loading | Run `php artisan storage:link` |
| CSS/JS not loading | Check `APP_URL` matches your domain |
| Permission denied | Run `chmod -R 775 storage bootstrap/cache` |
| Queue not processing | Set up Supervisor or use `php artisan queue:work` |

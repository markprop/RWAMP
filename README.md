# RWAMP Laravel Application

A Laravel 10+ application for RWAMP – The Currency of Real Estate Investments.

## 🚀 Features

- Server‑side rendered Blade templates
- Modern frontend: TailwindCSS + Alpine.js
- Forms: Contact, Reseller, Newsletter (AJAX + classic POST)
- Email notifications (non‑blocking; failures don’t break UX)
- Database models + service layer
- Responsive design, animations, and componentized UI
- Role‑based dashboards (Investor, Reseller, Admin)
- Admin 2FA (Laravel Fortify) with enforced access to Admin dashboard
- Security hardening: CSRF, throttling, honeypots, security headers (CSP), input validation
- SEO: unique titles/descriptions, canonical, Open Graph/Twitter cards, JSON‑LD, robots.txt & dynamic sitemap.xml

## 🛠️ Tech Stack

- Backend: Laravel 10+ (PHP 8.1+)
- Frontend: Blade + Alpine.js
- Styling: TailwindCSS
- Build Tool: Vite
- Database: MySQL/SQLite
- Email: Laravel Mail (smtp or log in dev)

## 📁 Project Structure

```
rwamp-laravel/
├── app/
│   ├── Http/Controllers/     # Controllers
│   ├── Models/              # Eloquent models
│   └── Services/            # Business logic
├── resources/
│   ├── views/               # Blade templates
│   │   ├── layouts/         # Layout templates
│   │   ├── pages/           # Page views
│   │   └── components/      # Reusable components
│   ├── css/                 # Stylesheets
│   └── js/                  # JavaScript
├── public/                  # Public assets
├── routes/                  # Route definitions
└── database/               # Migrations
```

## 🚀 Installation

1. **Install Dependencies**:
   ```bash
   composer install
   npm install
   ```

2. **Environment Setup**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Setup**:
   ```bash
   php artisan migrate
   ```

4. **Build Assets**:
   ```bash
   npm run build
   # or for development:
   npm run dev
   ```

5. **Start Server**:
   ```bash
   php artisan serve
   ```

## 📝 Configuration

### Environment Variables

Update `.env` file with your settings:

```env
APP_NAME=RWAMP
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rwamp_laravel
DB_USERNAME=your_username
DB_PASSWORD=your_password

MAIL_MAILER=log # use smtp in production
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=hello@rwamp.com
MAIL_FROM_NAME="RWAMP"

# Analytics (optional)
GOOGLE_ANALYTICS_ID=your_ga_id
META_PIXEL_ID=your_pixel_id
ADMIN_EMAIL=admin@rwamp.com

# reCAPTCHA v3 (optional; used on Contact + Reseller)
RECAPTCHA_SITE_KEY=your_recaptcha_site_key
RECAPTCHA_SECRET_KEY=your_recaptcha_secret_key
RECAPTCHA_MIN_SCORE=0.5

# Fortify (2FA)
APP_ENV=production
APP_DEBUG=false
```

### Database Tables

The application includes these tables:
- `users` – now with `role`, `phone`, `company_name`, `investment_capacity`, `experience`, and Fortify 2FA columns
- `contacts` – contact form submissions
- `reseller_applications` – reseller program applications
- `newsletter_subscriptions` – newsletter subscribers
- `password_reset_tokens` – password reset support

## 🎨 Customization

### Styling
- Edit `resources/css/app.css` for custom styles
- Update `tailwind.config.js` for theme customization
- Modify component files in `resources/views/components/`

### Content
- Update text content in Blade templates
- Modify form fields in controllers
- Adjust email templates in `resources/views/emails/`

### Features
- Add new pages by creating controllers and views
- Extend services for additional business logic
- Add new models for additional data storage

## 📧 Email Templates

Create email templates in `resources/views/emails/`:
- `contact-notification.blade.php`
- `contact-confirmation.blade.php`
- `reseller-notification.blade.php`
- `reseller-confirmation.blade.php`
- `newsletter-welcome.blade.php`

## 🔧 Development

### Available Commands

```bash
# Development
npm run dev          # Start Vite dev server
php artisan serve    # Start Laravel server

# Production
npm run build        # Build assets for production
php artisan optimize # Optimize for production

# Database
php artisan migrate        # Run migrations
php artisan migrate:fresh  # Fresh migration with seeders
php artisan db:seed        # Run seeders

# Cache
php artisan cache:clear    # Clear application cache
php artisan config:clear   # Clear configuration cache
php artisan view:clear     # Clear view cache
```

### Adding New Features

1. **New Page**: Create controller → Add route → Create view
2. **New Form**: Create model → Add migration → Create service → Add controller
3. **New Component**: Create Blade component in `resources/views/components/`

## 🚀 Deployment

### For Hostinger Shared Hosting

1. **Upload Files**: Upload all files to `public_html`
2. **Database**: Create MySQL database and import schema
3. **Environment**: Update `.env` with production settings
4. **Assets**: Run `npm run build` and upload `public/build/`
5. **Permissions**: Set proper file permissions

### For VPS/Dedicated Server

1. **Server Setup**: Install PHP 8.1+, Composer, Node.js
2. **Web Server**: Configure Apache/Nginx
3. **SSL**: Install SSL certificate
4. **Database**: Setup MySQL/PostgreSQL
5. **Deploy**: Use Laravel Forge, Envoyer, or manual deployment

## 📱 Mobile Optimization

- Responsive design with TailwindCSS
- Touch-friendly buttons and forms
- Optimized images and animations
- Fast loading with Vite
- Progressive Web App ready

## 🔒 Security & Hardening

- CSRF protection on all forms
- Input validation across controllers; non‑blocking email
- SQL injection prevention via Eloquent
- XSS protection via Blade escaping
- Rate limiting: login (5/min), contact/reseller (3/hour), newsletter (6/hour)
- Honeypots on Newsletter/Reseller/Contact
- Security headers middleware: CSP, X‑Frame‑Options, Referrer‑Policy, etc.
- 2FA (Fortify) enforced for Admin dashboard

## 📊 SEO & Analytics

- SEO:
  - Unique titles/descriptions per page (passed from controllers/routes)
  - Canonical link, Open Graph & Twitter cards
  - Organization JSON‑LD in layout
  - robots.txt and dynamic sitemap.xml (includes main pages; auto‑adds blog/news/projects/docs if models exist)
- Analytics:
  - Google Analytics (optional)
  - Meta Pixel (optional)

## 📚 Documentation

- Admin 2FA: [`docs/admin-2fa.md`](docs/admin-2fa.md)
- SEO Setup: [`docs/seo.md`](docs/seo.md)
 - Security: [`docs/security.md`](docs/security.md)
 - Forms & Services: [`docs/forms.md`](docs/forms.md)
 - Auth & Roles: [`docs/auth-roles.md`](docs/auth-roles.md)

## 🔐 Role‑based Auth & 2FA

- Roles: `investor`, `reseller`, `admin`
- Dashboards:
  - `/dashboard/investor`, `/dashboard/reseller`, `/dashboard/admin`
- Login redirects by role; Navbar shows a role‑aware Dashboard link
- Admin 2FA:
  - Enable via `/admin/2fa/setup` → scan QR → save recovery codes
  - Enforced before accessing `/dashboard/admin`
  - Admin dashboard shows 2FA status badge

## 📨 Forms

- Classic POST endpoints (Contact/Reseller) and Alpine.js AJAX (Newsletter/Reseller)
- reCAPTCHA v3 (optional) on Contact + Reseller
- Honeypot field `hp` across forms; server validates `max:0`
- Flash messages on success/error

## 📄 Public Pages

- Home `/` (components/hero/about/why‑invest/reseller/roadmap/signup)
- About `/about`
- Contact `/contact`
- Legal: `/privacy-policy`, `/terms-of-service`, `/disclaimer`
- Purchase `/purchase` (auth required)

## 🧭 SEO Meta Usage

Pass the following variables from controllers/routes to override defaults in `layouts/app.blade.php`:

`title`, `description`, `ogTitle`, `ogDescription`, `ogImage`, `twitterTitle`, `twitterDescription`, `twitterImage`

## 👤 Admin Dashboard Metrics

- Total Users, Investors, Resellers
- New Users (7d / 30d)
- Contacts Count
- Reseller Applications (pending/approved/rejected) + latest 10 table
- Coin Price (placeholder Rs 0.70/token; can be moved to DB or API)

## 🧪 Local Development Tips (Windows PowerShell)

- Chain commands with `;` not `&&`:
  - `php artisan config:clear; php artisan cache:clear; php artisan optimize:clear`

## 🤝 Contributing

1. Fork the repository
2. Create feature branch
3. Make changes
4. Test thoroughly
5. Submit pull request

## 📄 License

This project is proprietary software owned by RWAMP.

## 🆘 Support

For support and questions:
- Email: info@rwamp.com
- Phone: +92 300 1234567
- Website: https://rwamp.com

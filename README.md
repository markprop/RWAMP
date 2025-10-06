<p align="center">
  <img src="https://capsule-render.vercel.app/api?type=waving&color=0:0ea5e9,100:10b981&height=180&section=header&text=RWAMP%20Laravel&fontSize=42&fontColor=ffffff&animation=twinkling&fontAlignY=35" alt="RWAMP Laravel" />
</p>

<p align="center">
  <img src="public/images/logo.jpeg" alt="RWAMP Logo" height="72" />
</p>

<p align="center">
  <img src="https://readme-typing-svg.demolab.com?font=Inter&size=20&duration=3500&pause=800&color=10B981&center=true&vCenter=true&width=800&lines=The+Currency+of+Real+Estate+Investments;Laravel+%2B+Vite+%2B+Tailwind;Clean+architecture+with+services+%26+Blade" alt="Typing SVG" />
</p>

<p align="center">
  <a href="#-features"><img src="https://img.shields.io/badge/Laravel-10%2B-FF2D20?logo=laravel&logoColor=white" alt="Laravel" /></a>
  <a href="#-tech-stack"><img src="https://img.shields.io/badge/PHP-8.1+-777BB4?logo=php&logoColor=white" alt="PHP" /></a>
  <a href="#-tech-stack"><img src="https://img.shields.io/badge/Vite-Build-646CFF?logo=vite&logoColor=white" alt="Vite" /></a>
  <a href="#-tech-stack"><img src="https://img.shields.io/badge/Tailwind-CSS-06B6D4?logo=tailwindcss&logoColor=white" alt="TailwindCSS" /></a>
  <a href="#-installation"><img src="https://img.shields.io/badge/Status-Active-10B981?style=flat" alt="Status" /></a>
</p>

<br />

## ✨ Overview

RWAMP Laravel is a modern Laravel 10+ application for RWAMP — the currency of real estate investments — migrated from Next.js and engineered for performance, maintainability, and a delightful developer experience.

---

## 📚 Table of Contents

- [✨ Overview](#-overview)
- [🚀 Features](#-features)
- [🛠️ Tech Stack](#%EF%B8%8F-tech-stack)
- [📁 Project Structure](#-project-structure)
- [⚙️ Installation](#%EF%B8%8F-installation)
- [📝 Configuration](#-configuration)
- [🧪 Scripts](#-scripts)
- [🚢 Deployment](#-deployment)
- [🔐 Security](#-security)
- [📊 Analytics](#-analytics)
- [🤝 Contributing](#-contributing)
- [📄 License](#-license)
- [🆘 Support](#-support)

---

## 🚀 Features

- **Server-rendered** Blade pages with semantic, SEO-friendly markup
- **Modern UI** with TailwindCSS and Alpine.js micro-interactions
- **Forms** for contact, reseller applications, and newsletter subscriptions
- **Email workflows** with notifications and confirmations
- **Clean architecture** via service layer and Eloquent models
- **Production-ready** asset pipeline powered by Vite

## 🛠️ Tech Stack

- **Backend**: Laravel 10+ (PHP 8.1+)
- **Frontend**: Blade + Alpine.js
- **Styling**: TailwindCSS
- **Build Tool**: Vite
- **Database**: MySQL/PostgreSQL/SQLite
- **Email**: Laravel Mail

## 📁 Project Structure

```
rwamp-laravel/
├── app/
│   ├── Http/Controllers/     # Controllers
│   ├── Models/               # Eloquent models
│   └── Services/             # Domain services
├── resources/
│   ├── views/                # Blade templates
│   │   ├── layouts/
│   │   ├── pages/
│   │   └── components/
│   ├── css/                  # Stylesheets
│   └── js/                   # JavaScript
├── public/                   # Public assets
├── routes/                   # Route definitions
└── database/                 # Migrations
```

## ⚙️ Installation

1. Install dependencies
   ```bash
   composer install
   npm install
   ```
2. Configure environment
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. Run database migrations
   ```bash
   php artisan migrate
   ```
4. Build or serve assets
   ```bash
   npm run build    # production
   # or
   npm run dev      # development with HMR
   ```
5. Start the app
   ```bash
   php artisan serve
   ```

## 📝 Configuration

Add and adjust values in `.env`:

```env
APP_NAME=RWAMP
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rwamp_laravel
DB_USERNAME=your_username
DB_PASSWORD=your_password

MAIL_MAILER=smtp
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
```

## 🧪 Scripts

```bash
# Dev
npm run dev           # Vite dev server
php artisan serve     # Laravel server

# Build/optimize
npm run build         # Production build
php artisan optimize  # Cache config/routes/views

# Database
php artisan migrate        # Run migrations
php artisan migrate:fresh  # Fresh DB (drops + migrates)
php artisan db:seed        # Seed database

# Cache management
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## 🚢 Deployment

General production checklist:

- `composer install --no-dev --prefer-dist --optimize-autoloader`
- `php artisan migrate --force`
- `npm ci && npm run build`
- `php artisan config:cache route:cache view:cache`
- Ensure correct web server docroot points to `public/`

### Shared Hosting (e.g. Hostinger)
- Upload application and set `public/` as web root (or move `index.php` accordingly)
- Create database, import schema, update `.env`
- Upload built assets from `public/build/`

### VPS / Dedicated
- Install PHP 8.1+, Composer, Node.js LTS
- Configure Nginx/Apache (docroot `public/`)
- Enable SSL (Let’s Encrypt or provider)
- Set up zero-downtime deploys (Forge/Envoyer) if desired

## 🔐 Security

- CSRF protection on all forms
- Input validation and sanitization
- Eloquent guards against SQL injection
- Blade auto-escapes to help prevent XSS
- Rate limiting for sensitive endpoints

## 📊 Analytics

- Google Analytics and Meta Pixel ready
- Track conversions and form submissions

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make changes with tests where relevant
4. Ensure `npm run build` and `php artisan test` pass
5. Open a pull request

## 📄 License

This project is proprietary software owned by Mark Properties.

## 🆘 Support

- Email: info@rwamp.com
- Phone: +92 300 1234567
- Website: https://rwamp.com

<p align="center">
  <img src="https://capsule-render.vercel.app/api?type=waving&color=0:0ea5e9,100:10b981&height=120&section=footer&animation=twinkling" alt="footer wave" />
</p>

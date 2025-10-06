# RWAMP Laravel Application

A Laravel 10+ application for RWAMP - The Currency of Real Estate Investments, migrated from Next.js.

## 🚀 Features

- **Server-Side Rendering**: Clean, fast Blade templates
- **Modern Frontend**: TailwindCSS + Alpine.js for interactivity
- **Form Handling**: Contact, reseller, and newsletter forms
- **Email Notifications**: Automated email responses
- **Database Integration**: Eloquent models for data management
- **Service Layer**: Clean business logic separation
- **Responsive Design**: Mobile-first approach
- **SEO Optimized**: Meta tags and structured data

## 🛠️ Tech Stack

- **Backend**: Laravel 10+ (PHP 8.1+)
- **Frontend**: Blade templates + Alpine.js
- **Styling**: TailwindCSS
- **Build Tool**: Vite
- **Database**: MySQL/PostgreSQL
- **Email**: Laravel Mail

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

MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=hello@rwamp.com
MAIL_FROM_NAME="RWAMP"

# Analytics
GOOGLE_ANALYTICS_ID=your_ga_id
META_PIXEL_ID=your_pixel_id
ADMIN_EMAIL=admin@rwamp.com
```

### Database Tables

The application includes these tables:
- `contacts` - Contact form submissions
- `reseller_applications` - Reseller program applications
- `newsletter_subscriptions` - Newsletter subscribers

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

## 🔒 Security Features

- CSRF protection on all forms
- Input validation and sanitization
- SQL injection prevention with Eloquent
- XSS protection with Blade templating
- Rate limiting on API endpoints

## 📊 Analytics Integration

- Google Analytics ready
- Meta Pixel integration
- Custom event tracking
- Form submission tracking

## 🤝 Contributing

1. Fork the repository
2. Create feature branch
3. Make changes
4. Test thoroughly
5. Submit pull request

## 📄 License

This project is proprietary software owned by Mark Properties.

## 🆘 Support

For support and questions:
- Email: info@rwamp.com
- Phone: +92 300 1234567
- Website: https://rwamp.com

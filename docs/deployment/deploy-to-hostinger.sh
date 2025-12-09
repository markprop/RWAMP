#!/bin/bash

# RWAMP Laravel - Hostinger Deployment Script
# This script helps automate the deployment process

set -e  # Exit on error

echo "🚀 RWAMP Laravel - Hostinger Deployment Script"
echo "=============================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_info() {
    echo -e "${YELLOW}ℹ️  $1${NC}"
}

# Check if we're in the project root
if [ ! -f "artisan" ]; then
    print_error "Please run this script from the Laravel project root directory"
    exit 1
fi

echo "Step 1: Building assets for production..."
if command -v npm &> /dev/null; then
    npm run build
    print_success "Assets built successfully"
else
    print_warning "npm not found. Please build assets manually: npm run build"
fi

echo ""
echo "Step 2: Optimizing Laravel..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
print_success "Laravel optimized"

echo ""
echo "Step 3: Checking file permissions..."
if [ -d "storage" ]; then
    chmod -R 775 storage
    print_success "Storage permissions set"
else
    print_error "Storage directory not found!"
    exit 1
fi

if [ -d "bootstrap/cache" ]; then
    chmod -R 775 bootstrap/cache
    print_success "Bootstrap cache permissions set"
else
    print_error "Bootstrap cache directory not found!"
    exit 1
fi

echo ""
echo "Step 4: Creating storage symlink..."
if [ ! -L "public/storage" ]; then
    php artisan storage:link
    print_success "Storage symlink created"
else
    print_info "Storage symlink already exists"
fi

echo ""
echo "Step 5: Checking .env file..."
if [ ! -f ".env" ]; then
    print_warning ".env file not found. Please create it from .env.example"
    print_info "Run: cp .env.example .env"
    print_info "Then: php artisan key:generate"
else
    print_success ".env file exists"
    
    # Check if APP_KEY is set
    if grep -q "APP_KEY=base64:" .env; then
        print_success "APP_KEY is set"
    else
        print_warning "APP_KEY not set. Generating..."
        php artisan key:generate
    fi
fi

echo ""
echo "Step 6: Checking database connection..."
if php artisan migrate:status &> /dev/null; then
    print_success "Database connection successful"
else
    print_error "Database connection failed. Please check your .env file"
    exit 1
fi

echo ""
echo "Step 7: Running migrations..."
read -p "Run database migrations? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan migrate --force
    print_success "Migrations completed"
else
    print_info "Skipping migrations"
fi

echo ""
echo "Step 8: Optimizing autoloader..."
if command -v composer &> /dev/null; then
    composer dump-autoload --optimize
    print_success "Autoloader optimized"
else
    print_warning "Composer not found. Please run: composer dump-autoload --optimize"
fi

echo ""
echo "=============================================="
print_success "Deployment preparation complete!"
echo ""
echo "Next steps:"
echo "1. Upload all files to your Hostinger server"
echo "2. Ensure file permissions are correct (775 for storage, 644 for files)"
echo "3. Verify .env file is configured correctly"
echo "4. Test your application: https://yourdomain.com"
echo ""
echo "For detailed instructions, see: HOSTINGER_DEPLOYMENT_GUIDE.md"


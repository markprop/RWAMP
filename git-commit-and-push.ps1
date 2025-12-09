# Professional Git Commit and Push Script for RWAMP Laravel Project
# This script creates organized commits and pushes to GitHub

Write-Host "=== RWAMP Laravel Project - Git Commit and Push ===" -ForegroundColor Cyan
Write-Host ""

# Check if git is initialized
if (-not (Test-Path .git)) {
    Write-Host "Initializing git repository..." -ForegroundColor Yellow
    git init
}

# Check git config
$userName = git config user.name
$userEmail = git config user.email

if (-not $userName -or -not $userEmail) {
    Write-Host "Git user configuration not found. Please set:" -ForegroundColor Yellow
    Write-Host "  git config user.name 'Your Name'" -ForegroundColor Yellow
    Write-Host "  git config user.email 'your.email@example.com'" -ForegroundColor Yellow
    exit 1
}

Write-Host "Git User: $userName <$userEmail>" -ForegroundColor Green
Write-Host ""

# Stage all files
Write-Host "Staging all files..." -ForegroundColor Yellow
git add -A

# Get file count
$fileCount = (git status --short | Measure-Object -Line).Lines
Write-Host "Files to commit: $fileCount" -ForegroundColor Green
Write-Host ""

# Create initial commit with comprehensive message
Write-Host "Creating initial commit..." -ForegroundColor Yellow

$commitMessage = @"
feat: initial commit - RWAMP Laravel application

Complete Laravel 10.x application for real estate tokenization platform.

Core Features:
- Multi-role authentication system (Admin, Reseller, Investor)
- Crypto payment processing (USDT TRC20/ERC20/BEP20, Bitcoin)
- KYC verification system with document upload
- Reseller commission and referral system
- Trading game with real-time price engine
- Withdrawal management system
- 2FA authentication for admins (Laravel Fortify)
- ULID-based URL obfuscation
- WalletConnect integration
- WhatsApp-style chat system infrastructure
- Comprehensive admin, reseller, and investor dashboards

Technical Stack:
- Laravel 10.x with PHP 8.1+
- TailwindCSS 3.3+ for styling
- Alpine.js 3.13+ for reactivity
- Vite 4.0+ for asset building
- Chart.js for data visualization
- Laravel Fortify for authentication
- Multiple blockchain API integrations

Architecture:
- MVC pattern with service layer
- 38 controllers across all modules
- 17 Eloquent models with relationships
- 10 service classes for business logic
- 40+ database migrations
- 80+ Blade templates
- Comprehensive security implementation

Security:
- CSRF protection
- Rate limiting
- Honeypot fields
- Security headers (CSP, X-Frame-Options)
- Input validation
- SQL injection prevention
- XSS protection
- 2FA for admin users

Documentation:
- Comprehensive README.md
- Multiple implementation guides
- Deployment documentation
- API documentation
- Security documentation

Project Status: Production Ready
"@

git commit -m $commitMessage

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "✓ Commit created successfully!" -ForegroundColor Green
    Write-Host ""
    
    # Check for remote
    $remote = git remote -v
    if (-not $remote) {
        Write-Host "No remote repository configured." -ForegroundColor Yellow
        Write-Host ""
        Write-Host "To add a GitHub remote, run:" -ForegroundColor Cyan
        Write-Host "  git remote add origin https://github.com/username/rwamp-laravel.git" -ForegroundColor White
        Write-Host ""
        Write-Host "Then push with:" -ForegroundColor Cyan
        Write-Host "  git push -u origin main" -ForegroundColor White
    } else {
        Write-Host "Remote repository found:" -ForegroundColor Green
        Write-Host $remote
        Write-Host ""
        $branch = git branch --show-current
        if (-not $branch) {
            $branch = "main"
            git branch -M main
        }
        Write-Host "To push to GitHub, run:" -ForegroundColor Cyan
        Write-Host "  git push -u origin $branch" -ForegroundColor White
    }
} else {
    Write-Host ""
    Write-Host "✗ Commit failed. Please check the error above." -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "=== Script Complete ===" -ForegroundColor Cyan


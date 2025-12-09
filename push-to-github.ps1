# Push RWAMP Laravel Project to GitHub
# This script helps you push your commits to GitHub

Write-Host "=== RWAMP Laravel - Push to GitHub ===" -ForegroundColor Cyan
Write-Host ""

# Check current branch
$branch = git branch --show-current
if (-not $branch) {
    $branch = "main"
    git branch -M main
    Write-Host "Created and switched to 'main' branch" -ForegroundColor Yellow
}

Write-Host "Current branch: $branch" -ForegroundColor Green
Write-Host ""

# Check for remote
$remote = git remote -v
if (-not $remote) {
    Write-Host "No remote repository configured." -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Please provide your GitHub repository URL:" -ForegroundColor Cyan
    Write-Host "  Example: https://github.com/username/rwamp-laravel.git" -ForegroundColor White
    Write-Host "  Or SSH: git@github.com:username/rwamp-laravel.git" -ForegroundColor White
    Write-Host ""
    
    $repoUrl = Read-Host "https://github.com/username/rwamp-laravel.git"
    
    if ($repoUrl) {
        git remote add origin $repoUrl
        Write-Host "Remote 'origin' added: $repoUrl" -ForegroundColor Green
    } else {
        Write-Host ""
        Write-Host "To add remote manually, run:" -ForegroundColor Cyan
        Write-Host "  git remote add origin https://github.com/username/rwamp-laravel.git" -ForegroundColor White
        Write-Host ""
        Write-Host "Then push with:" -ForegroundColor Cyan
        Write-Host "  git push -u origin $branch" -ForegroundColor White
        exit 0
    }
} else {
    Write-Host "Remote repository found:" -ForegroundColor Green
    Write-Host $remote
    Write-Host ""
}

# Check if there are commits to push
$commitsAhead = git rev-list --count origin/$branch..HEAD 2>$null
if ($LASTEXITCODE -ne 0) {
    $commitsAhead = (git log --oneline | Measure-Object -Line).Lines
    Write-Host "Local commits to push: $commitsAhead" -ForegroundColor Yellow
} else {
    Write-Host "Commits ahead of remote: $commitsAhead" -ForegroundColor Yellow
}

Write-Host ""

# Ask for confirmation
$confirm = Read-Host "Do you want to push to GitHub? (y/n)"
if ($confirm -ne 'y' -and $confirm -ne 'Y') {
    Write-Host "Push cancelled." -ForegroundColor Yellow
    exit 0
}

Write-Host ""
Write-Host "Pushing to GitHub..." -ForegroundColor Yellow
Write-Host ""

# Push to GitHub
git push -u origin $branch

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "✓ Successfully pushed to GitHub!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Your repository is now available on GitHub." -ForegroundColor Cyan
} else {
    Write-Host ""
    Write-Host "✗ Push failed. Please check the error above." -ForegroundColor Red
    Write-Host ""
    Write-Host "Common issues:" -ForegroundColor Yellow
    Write-Host "  1. Authentication required - use GitHub CLI or personal access token" -ForegroundColor White
    Write-Host "  2. Repository doesn't exist - create it on GitHub first" -ForegroundColor White
    Write-Host "  3. Branch name mismatch - ensure remote branch exists" -ForegroundColor White
}

Write-Host ""
Write-Host "=== Complete ===" -ForegroundColor Cyan


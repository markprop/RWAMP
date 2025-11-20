# Script to change default branch on GitHub
# You need a Personal Access Token with repo permissions

$repoOwner = "markprop"
$repoName = "RWAMP"
$newDefaultBranch = "master"
$token = Read-Host "Enter your GitHub Personal Access Token (or press Enter to skip)"

if ([string]::IsNullOrWhiteSpace($token)) {
    Write-Host "`nTo change the default branch, you have two options:" -ForegroundColor Yellow
    Write-Host "`n1. Via GitHub Web Interface:" -ForegroundColor Cyan
    Write-Host "   - Go to: https://github.com/$repoOwner/$repoName/settings/branches" -ForegroundColor White
    Write-Host "   - Scroll to the TOP of the page (above Branch protection rules)" -ForegroundColor White
    Write-Host "   - Look for 'Default branch' section" -ForegroundColor White
    Write-Host "   - Click the switch/edit icon next to 'main'" -ForegroundColor White
    Write-Host "   - Select 'master' from dropdown" -ForegroundColor White
    Write-Host "   - Click 'Update' and confirm" -ForegroundColor White
    
    Write-Host "`n2. Via GitHub API (requires Personal Access Token):" -ForegroundColor Cyan
    Write-Host "   - Create token at: https://github.com/settings/tokens" -ForegroundColor White
    Write-Host "   - Token needs 'repo' scope" -ForegroundColor White
    Write-Host "   - Run this script again and enter the token" -ForegroundColor White
    exit
}

try {
    $headers = @{
        "Authorization" = "token $token"
        "Accept" = "application/vnd.github.v3+json"
    }
    
    $body = @{
        default_branch = $newDefaultBranch
    } | ConvertTo-Json
    
    $uri = "https://api.github.com/repos/$repoOwner/$repoName"
    
    Write-Host "`nChanging default branch from 'main' to 'master'..." -ForegroundColor Yellow
    
    $response = Invoke-RestMethod -Uri $uri -Method PATCH -Headers $headers -Body $body -ContentType "application/json"
    
    Write-Host "`n✅ Success! Default branch changed to 'master'" -ForegroundColor Green
    Write-Host "Repository URL: $($response.html_url)" -ForegroundColor Cyan
    Write-Host "Default branch: $($response.default_branch)" -ForegroundColor Cyan
} catch {
    Write-Host "`n❌ Error: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.Exception.Response.StatusCode -eq 401) {
        Write-Host "Authentication failed. Please check your token." -ForegroundColor Yellow
    } elseif ($_.Exception.Response.StatusCode -eq 403) {
        Write-Host "Permission denied. Make sure your token has 'repo' scope." -ForegroundColor Yellow
    }
}


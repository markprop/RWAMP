# PowerShell script to fix .env file
# Run this script: .\fix-env.ps1

$envFile = ".env"
$backupFile = ".env.backup.$(Get-Date -Format 'yyyyMMdd-HHmmss')"

Write-Host "Creating backup: $backupFile"
Copy-Item $envFile $backupFile

Write-Host "Reading .env file..."
$lines = Get-Content $envFile
$newLines = @()
$addedWalletConnect = $false
$addedPayments = $false

foreach ($line in $lines) {
    $newLines += $line
    
    # Add WALLETCONNECT_ENABLED after WALLETCONNECT_PROJECT_ID
    if ($line -match 'WALLETCONNECT_PROJECT_ID' -and -not $addedWalletConnect) {
        $newLines += 'WALLETCONNECT_ENABLED=true'
        $addedWalletConnect = $true
        Write-Host "Added WALLETCONNECT_ENABLED=true"
    }
    
    # Add payment configuration after TRONGRID_API_KEY
    if ($line -match 'TRONGRID_API_KEY' -and -not $addedPayments) {
        $newLines += ''
        $newLines += '# Crypto Payments Configuration'
        $newLines += 'CRYPTO_PAYMENTS_ENABLED=true'
        $newLines += 'STATIC_PAYMENT_DISABLED=true'
        $newLines += 'CRYPTO_WALLET_BEP20=0x3fB2e0f8C575eee0a3dE43cC3B69288644cD7B03'
        $addedPayments = $true
        Write-Host "Added payment configuration"
    }
}

Write-Host "Writing updated .env file..."
$newLines | Set-Content $envFile -Encoding UTF8

Write-Host ""
Write-Host "✅ .env file updated successfully!"
Write-Host "Backup saved as: $backupFile"
Write-Host ""
Write-Host "Next steps:"
Write-Host "1. Run: php artisan config:clear"
Write-Host "2. Run: php artisan cache:clear"
Write-Host "3. Refresh your browser"


# Laravel POS System Setup Script for Windows
# This script helps you set up the application using XAMPP

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Laravel POS System Setup" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Set PHP path (XAMPP)
$phpPath = "C:\xampp\php\php.exe"
$composerPath = "C:\xampp\php\composer.phar"

# Check if PHP exists
if (Test-Path $phpPath) {
    Write-Host "✓ Found PHP at: $phpPath" -ForegroundColor Green
} else {
    Write-Host "✗ PHP not found at: $phpPath" -ForegroundColor Red
    Write-Host "Please install XAMPP or update the PHP path in this script" -ForegroundColor Yellow
    exit
}

# Check if Composer exists
$composerCmd = "composer"
if (Test-Path $composerPath) {
    Write-Host "✓ Found Composer at: $phpPath $composerPath" -ForegroundColor Green
    $composerCmd = "$phpPath $composerPath"
} elseif (Get-Command composer -ErrorAction SilentlyContinue) {
    Write-Host "✓ Found Composer in PATH" -ForegroundColor Green
    $composerCmd = "composer"
} else {
    Write-Host "✗ Composer not found" -ForegroundColor Red
    Write-Host "Please install Composer from: https://getcomposer.org/download/" -ForegroundColor Yellow
    Write-Host "Or download composer.phar to: $composerPath" -ForegroundColor Yellow
    exit
}

Write-Host ""
Write-Host "Step 1: Installing PHP dependencies..." -ForegroundColor Yellow
& $phpPath -r "if (file_exists('composer.phar')) { echo 'Using local composer.phar'; }"
Invoke-Expression "$composerCmd install"
if ($LASTEXITCODE -ne 0) {
    Write-Host "✗ Failed to install PHP dependencies" -ForegroundColor Red
    exit
}

Write-Host ""
Write-Host "Step 2: Creating .env file..." -ForegroundColor Yellow
if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    Write-Host "✓ .env file created" -ForegroundColor Green
} else {
    Write-Host "✓ .env file already exists" -ForegroundColor Green
}

Write-Host ""
Write-Host "Step 3: Configuring database..." -ForegroundColor Yellow
# Update .env for SQLite
$envContent = Get-Content ".env" -Raw
$envContent = $envContent -replace "DB_CONNECTION=.*", "DB_CONNECTION=sqlite"
$envContent = $envContent -replace "DB_DATABASE=.*", "DB_DATABASE=$PWD\database\database.sqlite"
Set-Content ".env" -Value $envContent
Write-Host "✓ Database configured for SQLite" -ForegroundColor Green

Write-Host ""
Write-Host "Step 4: Creating SQLite database..." -ForegroundColor Yellow
if (-not (Test-Path "database\database.sqlite")) {
    New-Item -ItemType File -Path "database\database.sqlite" -Force | Out-Null
    Write-Host "✓ SQLite database created" -ForegroundColor Green
} else {
    Write-Host "✓ SQLite database already exists" -ForegroundColor Green
}

Write-Host ""
Write-Host "Step 5: Generating application key..." -ForegroundColor Yellow
& $phpPath artisan key:generate
if ($LASTEXITCODE -ne 0) {
    Write-Host "✗ Failed to generate application key" -ForegroundColor Red
    exit
}

Write-Host ""
Write-Host "Step 6: Running database migrations..." -ForegroundColor Yellow
& $phpPath artisan migrate
if ($LASTEXITCODE -ne 0) {
    Write-Host "✗ Failed to run migrations" -ForegroundColor Red
    exit
}

Write-Host ""
Write-Host "Step 7: Seeding database..." -ForegroundColor Yellow
& $phpPath artisan db:seed
if ($LASTEXITCODE -ne 0) {
    Write-Host "✗ Failed to seed database" -ForegroundColor Red
    exit
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Setup Complete! ✓" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "To start the server, run:" -ForegroundColor Yellow
Write-Host "  C:\xampp\php\php.exe artisan serve" -ForegroundColor White
Write-Host ""
Write-Host "Then open: http://localhost:8000" -ForegroundColor Yellow
Write-Host ""
Write-Host "Default Login:" -ForegroundColor Yellow
Write-Host "  Admin: admin@chickenshop.com / admin123" -ForegroundColor White
Write-Host "  Cashier: cashier@chickenshop.com / cashier123" -ForegroundColor White
Write-Host ""


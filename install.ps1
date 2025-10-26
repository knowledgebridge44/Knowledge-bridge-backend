# Knowledge Bridge - Windows Installation Script
# Run this script from the backend_laravel directory

Write-Host "==================================" -ForegroundColor Cyan
Write-Host "Knowledge Bridge - Installation" -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""

# Check if we're in the correct directory
if (-Not (Test-Path ".\artisan")) {
    Write-Host "ERROR: artisan file not found!" -ForegroundColor Red
    Write-Host "Please run this script from the backend_laravel directory" -ForegroundColor Yellow
    Write-Host "Current directory: $(Get-Location)" -ForegroundColor Yellow
    exit 1
}

Write-Host "[1/7] Checking PHP..." -ForegroundColor Green
try {
    $phpVersion = php -v
    Write-Host "PHP is installed" -ForegroundColor Green
} catch {
    Write-Host "ERROR: PHP is not installed or not in PATH" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "[2/7] Checking Composer..." -ForegroundColor Green
try {
    $composerVersion = composer --version
    Write-Host "Composer is installed" -ForegroundColor Green
} catch {
    Write-Host "ERROR: Composer is not installed or not in PATH" -ForegroundColor Red
    Write-Host "Please install Composer from https://getcomposer.org" -ForegroundColor Yellow
    exit 1
}

Write-Host ""
Write-Host "[3/7] Installing dependencies..." -ForegroundColor Green
if (Test-Path ".\vendor") {
    Write-Host "Dependencies already installed, skipping..." -ForegroundColor Yellow
} else {
    Write-Host "Running composer install (this may take a few minutes)..." -ForegroundColor Yellow
    composer install --no-interaction
    if ($LASTEXITCODE -ne 0) {
        Write-Host "ERROR: Failed to install dependencies" -ForegroundColor Red
        exit 1
    }
    Write-Host "Dependencies installed successfully" -ForegroundColor Green
}

Write-Host ""
Write-Host "[4/7] Creating .env file..." -ForegroundColor Green
if (Test-Path ".\.env") {
    Write-Host ".env file already exists, skipping..." -ForegroundColor Yellow
} else {
    if (Test-Path ".\.env.example") {
        Copy-Item .env.example .env
        Write-Host ".env file created successfully" -ForegroundColor Green
    } else {
        Write-Host "ERROR: .env.example file not found!" -ForegroundColor Red
        exit 1
    }
}

Write-Host ""
Write-Host "[5/7] Generating application key..." -ForegroundColor Green
php artisan key:generate
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR: Failed to generate application key" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "[6/7] Creating SQLite database..." -ForegroundColor Green
$dbPath = "database\database.sqlite"
if (Test-Path $dbPath) {
    Write-Host "Database file already exists" -ForegroundColor Yellow
} else {
    New-Item -ItemType File -Path $dbPath -Force | Out-Null
    Write-Host "Database file created: $dbPath" -ForegroundColor Green
}

Write-Host ""
Write-Host "[7/7] Running migrations and seeding database..." -ForegroundColor Green
php artisan migrate --force
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR: Migrations failed" -ForegroundColor Red
    Write-Host "Make sure the database file exists and has proper permissions" -ForegroundColor Yellow
    exit 1
}

Write-Host "Seeding database with test data..." -ForegroundColor Yellow
php artisan db:seed
if ($LASTEXITCODE -ne 0) {
    Write-Host "WARNING: Seeding failed or partially completed" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "==================================" -ForegroundColor Cyan
Write-Host "Installation Complete!" -ForegroundColor Green
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Test Accounts:" -ForegroundColor Cyan
Write-Host "  Admin:    admin@knowledgebridge.com" -ForegroundColor White
Write-Host "  Teacher:  teacher@knowledgebridge.com" -ForegroundColor White
Write-Host "  Student:  student@knowledgebridge.com" -ForegroundColor White
Write-Host "  Graduate: graduate@knowledgebridge.com" -ForegroundColor White
Write-Host "  Password: password (for all accounts)" -ForegroundColor Yellow
Write-Host ""
Write-Host "To start the development server, run:" -ForegroundColor Cyan
Write-Host "  php artisan serve" -ForegroundColor White
Write-Host ""
Write-Host "API will be available at: http://localhost:8000/api" -ForegroundColor Green


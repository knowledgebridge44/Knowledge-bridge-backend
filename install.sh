#!/bin/bash
# Knowledge Bridge - Linux/Mac Installation Script
# Run this script from the backend_laravel directory

echo "=================================="
echo "Knowledge Bridge - Installation"
echo "=================================="
echo ""

# Check if we're in the correct directory
if [ ! -f "./artisan" ]; then
    echo "ERROR: artisan file not found!"
    echo "Please run this script from the backend_laravel directory"
    echo "Current directory: $(pwd)"
    exit 1
fi

echo "[1/6] Checking PHP..."
if command -v php &> /dev/null; then
    echo "PHP is installed: $(php -v | head -n 1)"
else
    echo "ERROR: PHP is not installed or not in PATH"
    exit 1
fi

echo ""
echo "[2/6] Creating .env file..."
if [ -f ".env" ]; then
    echo ".env file already exists, skipping..."
else
    if [ -f ".env.example" ]; then
        cp .env.example .env
        echo ".env file created successfully"
    else
        echo "ERROR: .env.example file not found!"
        exit 1
    fi
fi

echo ""
echo "[3/6] Generating application key..."
php artisan key:generate
if [ $? -ne 0 ]; then
    echo "ERROR: Failed to generate application key"
    exit 1
fi

echo ""
echo "[4/6] Creating SQLite database..."
DB_PATH="database/database.sqlite"
if [ -f "$DB_PATH" ]; then
    echo "Database file already exists"
else
    touch "$DB_PATH"
    echo "Database file created: $DB_PATH"
fi

echo ""
echo "[5/6] Setting permissions..."
chmod -R 775 storage bootstrap/cache
echo "Permissions set for storage and cache directories"

echo ""
echo "[6/6] Running migrations..."
php artisan migrate --force
if [ $? -ne 0 ]; then
    echo "ERROR: Migrations failed"
    echo "Make sure the database file exists and has proper permissions"
    exit 1
fi

echo ""
echo "[7/7] Seeding database with test data..."
php artisan db:seed
if [ $? -ne 0 ]; then
    echo "WARNING: Seeding failed or partially completed"
fi

echo ""
echo "=================================="
echo "Installation Complete!"
echo "=================================="
echo ""
echo "Test Accounts:"
echo "  Admin:    admin@knowledgebridge.com"
echo "  Teacher:  teacher@knowledgebridge.com"
echo "  Student:  student@knowledgebridge.com"
echo "  Graduate: graduate@knowledgebridge.com"
echo "  Password: password (for all accounts)"
echo ""
echo "To start the development server, run:"
echo "  php artisan serve"
echo ""
echo "API will be available at: http://localhost:8000/api"


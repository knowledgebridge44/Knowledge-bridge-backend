# Knowledge Bridge - Academic Platform API

A comprehensive RESTful API built with **Laravel 12** and **Sanctum** authentication for an academic platform featuring role-based access control, content management, Q&A forums, and analytics.

## Features

- **🔐 Authentication & Authorization**: Token-based authentication with role-based access control (Student, Graduate, Teacher, Admin)
- **📚 Course Management**: Create, manage, and enroll in courses
- **📖 Lesson System**: Upload lessons with approval workflow
- **📂 Material Management**: File upload/download for course materials  
- **❓ Q&A Forum**: Ask questions and provide answers
- **⭐ Rating System**: Rate and review lessons
- **💬 Comment System**: Comment on lessons and questions with nested replies
- **🚨 Moderation**: Report inappropriate content with admin moderation
- **📊 Analytics**: Comprehensive analytics for admins
- **🔒 Enrollment Control**: Secure access to course content based on enrollment

## Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- SQLite (or MySQL/PostgreSQL)

### Installation

#### Quick Install (Recommended)

Use the automated installation scripts (no need to run composer install separately):

**Windows (PowerShell):**
```powershell
cd backend_laravel
.\install.ps1
```

**Linux/Mac:**
```bash
cd backend_laravel
chmod +x install.sh
./install.sh
```

The script will automatically:
- ✓ Check for PHP and Composer
- ✓ Install all dependencies (`composer install`)
- ✓ Create `.env` file
- ✓ Generate application key
- ✓ Create SQLite database
- ✓ Run migrations and seed test data

#### Manual Installation

If you prefer to install manually:

1. **Clone and Install Dependencies**
   ```bash
   git clone <repository-url>
   cd backend_laravel
   composer install
   ```

2. **Environment Setup**
   ```bash
   # Windows (PowerShell)
   Copy-Item .env.example .env
   
   # Linux/Mac
   cp .env.example .env
   
   # Generate application key
   php artisan key:generate
   ```

3. **Database Setup**
   ```bash
   # Create the SQLite database file (if it doesn't exist)
   # Windows (PowerShell)
   New-Item -ItemType File -Path database/database.sqlite -Force
   
   # Linux/Mac
   touch database/database.sqlite
   
   # Run migrations and seed test data
   php artisan migrate --force
   php artisan db:seed
   ```

4. **Start Development Server**
   ```bash
   php artisan serve
   ```

The API will be available at `http://localhost:8000/api`

### Troubleshooting

**Error: "Could not open input file: artisan"**
- This means you're not in the project root directory
- Make sure you're in the `backend_laravel` folder (where the `artisan` file is located)
- Run: `cd backend_laravel` or navigate to your project directory

**Error: "Failed to open stream: No such file or directory in artisan"**
- This means dependencies haven't been installed
- Run: `composer install` before running any artisan commands
- Or use the automated installation script which handles this automatically

**Error: "composer: command not found"**
- Composer is not installed or not in your PATH
- Download and install from: https://getcomposer.org
- Windows: Use the installer; Linux/Mac: Follow the installation guide

**Migration Errors:**
- Ensure the `database/database.sqlite` file exists and has proper permissions
- Verify your `.env` file has: `DB_CONNECTION=sqlite` and `DB_DATABASE=database/database.sqlite`

**Permission Issues (Linux/Mac):**
```bash
chmod -R 775 storage bootstrap/cache
```

### Test Accounts
After seeding, you can use these accounts:
- **Admin**: `admin@knowledgebridge.com`
- **Teacher**: `teacher@knowledgebridge.com`
- **Student**: `student@knowledgebridge.com`
- **Graduate**: `graduate@knowledgebridge.com`

**Password for all accounts**: `password`

## API Documentation

See [API_DOCUMENTATION.md](API_DOCUMENTATION.md) for complete API reference.

### Quick API Examples

**Register:**
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"full_name":"John Doe","email":"john@example.com","password":"password123","password_confirmation":"password123"}'
```

**Login:**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"john@example.com","password":"password123"}'
```

**Get Courses (with token):**
```bash
curl -X GET http://localhost:8000/api/courses \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Architecture

### Database Schema
- **Users**: Role-based user management
- **Courses**: Course creation and management
- **Lessons**: Content with approval workflow
- **Materials**: File attachments for lessons
- **Enrollments**: User-course relationships
- **Questions**: Q&A forum posts
- **Comments**: Threaded discussions
- **Ratings**: Lesson reviews and ratings
- **Reports**: Content moderation system

### Authorization System
- **Policies**: Granular permissions for each model
- **Middleware**: Role-based route protection
- **Enrollment Checks**: Content access control

### Key Business Rules
1. **Enrollment Required**: Users must enroll to access course content
2. **Content Approval**: Teacher uploads require admin approval
3. **Role Restrictions**: 
   - Students/Graduates: Learn, ask, rate
   - Teachers: Create courses, upload content
   - Admins: Full system control
4. **One Rating Per User**: Prevents rating manipulation

## Testing

Run the test suite:
```bash
php artisan test
```

## Technology Stack

- **Framework**: Laravel 12
- **Authentication**: Laravel Sanctum
- **Database**: SQLite (configurable)
- **File Storage**: Local filesystem
- **Testing**: PHPUnit

## Project Structure

```
app/
├── Http/Controllers/     # API Controllers
├── Models/              # Eloquent Models  
├── Policies/            # Authorization Policies
├── Middleware/          # Custom Middleware
└── Providers/           # Service Providers

database/
├── migrations/          # Database Schema
├── factories/           # Model Factories
└── seeders/            # Test Data Seeders

routes/
└── api.php             # API Routes

tests/
└── Feature/            # Integration Tests
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Submit a pull request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

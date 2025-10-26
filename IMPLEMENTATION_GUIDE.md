# Knowledge Bridge - Complete Implementation Guide

## Overview

This document provides a comprehensive guide on how the Knowledge Bridge academic platform was implemented using **Laravel 12** and **Laravel Sanctum** for authentication. The project is a RESTful API that supports role-based access control, content management, Q&A forums, and analytics.

## Table of Contents

1. [Project Setup](#project-setup)
2. [Authentication System](#authentication-system)
3. [Database Design](#database-design)
4. [Models and Relationships](#models-and-relationships)
5. [Authorization System](#authorization-system)
6. [API Controllers](#api-controllers)
7. [Middleware Implementation](#middleware-implementation)
8. [File Structure](#file-structure)
9. [Testing](#testing)
10. [Deployment Considerations](#deployment-considerations)

---

## Project Setup

### 1. Laravel Installation and Initial Setup

```bash
# Create new Laravel project
composer create-project laravel/laravel knowledge_bridge

# Navigate to project directory
cd knowledge_bridge

# Install Laravel Sanctum
composer require laravel/sanctum

# Publish Sanctum configuration
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Generate application key
php artisan key:generate
```

### 2. Environment Configuration

The project uses SQLite for simplicity, configured in `.env`:

```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

### 3. Dependencies

Key dependencies in `composer.json`:
- **Laravel Framework**: ^12.0
- **Laravel Sanctum**: ^4.2 (for API authentication)
- **Laravel Tinker**: ^2.10.1 (for debugging)

---

## Authentication System

### 1. Sanctum Configuration

**File**: `config/sanctum.php`

Key configurations:
- **Stateful domains**: Configured for localhost development
- **Token expiration**: Set to `null` (no expiration)
- **Guards**: Uses `web` guard
- **Middleware**: Standard Laravel middleware stack

### 2. User Model Enhancement

**File**: `app/Models/User.php`

Enhanced the default Laravel User model with:
- **HasApiTokens trait**: Enables Sanctum token functionality
- **Role-based system**: Added `role` field with enum values
- **Relationship methods**: Connected to all related models
- **Helper methods**: `hasRole()` and `isEnrolledIn()`

```php
protected $fillable = [
    'full_name',
    'email', 
    'password',
    'role',
];

// Roles: student, graduate, teacher, admin
```

### 3. Authentication Controller

**File**: `app/Http/Controllers/AuthController.php`

Implements four main endpoints:
- **Register**: Creates new users (prevents self-registration as teacher/admin)
- **Login**: Authenticates and returns Sanctum token
- **Logout**: Revokes current access token
- **Me**: Returns authenticated user information

Key security features:
- Password validation using Laravel's Password rules
- Role restrictions on registration
- Proper token management

---

## Database Design

### 1. Migration Strategy

**Migration Files**: `database/migrations/`

The database was designed with the following tables:

#### Core Tables:
1. **users** (0001_01_01_000000): User management with roles
2. **courses** (2024_01_02): Course information
3. **enrollments** (2024_01_03): User-course relationships
4. **lessons** (2024_01_04): Course content with approval workflow
5. **materials** (2024_01_05): File attachments for lessons
6. **questions** (2024_01_06): Q&A forum posts
7. **comments** (2024_01_07): Threaded discussions
8. **ratings** (2024_01_08): Lesson reviews and ratings
9. **reports** (2024_01_09): Content moderation system
10. **personal_access_tokens** (2025_09_24): Sanctum tokens

#### Key Schema Features:

**Users Table**:
```php
$table->id();
$table->string('full_name');
$table->string('email')->unique();
$table->string('password');
$table->enum('role', ['student', 'graduate', 'teacher', 'admin'])->default('student');
$table->timestamps();
```

**Lessons Table** (with approval workflow):
```php
$table->id();
$table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
$table->string('title');
$table->longText('content');
$table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
$table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
$table->timestamps();
```

### 2. Database Relationships

The system implements a comprehensive relationship structure:
- **One-to-Many**: User → Courses, Courses → Lessons, Lessons → Materials
- **Many-to-Many**: Users ↔ Courses (through enrollments)
- **Polymorphic**: Comments can belong to lessons or questions
- **Self-referencing**: Comments can have parent comments (threading)

---

## Models and Relationships

### 1. User Model Relationships

**File**: `app/Models/User.php`

```php
// Content creation relationships
public function createdCourses(): HasMany
public function uploadedLessons(): HasMany
public function uploadedMaterials(): HasMany

// Learning relationships  
public function enrolledCourses(): BelongsToMany
public function questions(): HasMany
public function comments(): HasMany
public function ratings(): HasMany
public function reports(): HasMany
```

### 2. Course Model

**File**: `app/Models/Course.php`

```php
public function creator(): BelongsTo
public function lessons(): HasMany
public function enrolledUsers(): BelongsToMany
public function approvedLessons(): HasMany // Only approved lessons
```

### 3. Lesson Model

**File**: `app/Models/Lesson.php`

```php
public function course(): BelongsTo
public function uploader(): BelongsTo
public function materials(): HasMany
public function comments(): HasMany
public function ratings(): HasMany
public function questions(): HasMany

// Helper methods
public function isApproved(): bool
public function averageRating(): float
```

### 4. Other Models

Each model follows Laravel conventions with:
- **Proper fillable attributes**
- **Relationship definitions**
- **Helper methods for business logic**
- **Factory support for testing**

---

## Authorization System

### 1. Policy-Based Authorization

**Files**: `app/Policies/`

Implemented comprehensive policies for each model:
- **CoursePolicy**: Controls course CRUD operations
- **LessonPolicy**: Manages lesson access and approval
- **MaterialPolicy**: File access control
- **QuestionPolicy**: Q&A permissions
- **CommentPolicy**: Discussion moderation
- **RatingPolicy**: Rating restrictions
- **ReportPolicy**: Moderation tools

#### Example: Course Policy

**File**: `app/Policies/CoursePolicy.php`

```php
public function create(User $user): bool
{
    return in_array($user->role, ['teacher', 'admin']);
}

public function update(User $user, Course $course): bool
{
    return $user->hasRole('admin') || 
           ($user->hasRole('teacher') && $course->created_by === $user->id);
}
```

### 2. Role-Based Middleware

**File**: `app/Http/Middleware/CheckRole.php`

Custom middleware for role-based route protection:

```php
public function handle(Request $request, Closure $next, string $role): Response
{
    if (!$request->user() || !$request->user()->hasRole($role)) {
        return response()->json([
            'error' => true,
            'message' => 'Forbidden',
            'code' => 403
        ], 403);
    }
    return $next($request);
}
```

### 3. Policy Registration

**File**: `app/Providers/AuthServiceProvider.php`

All policies are registered in the service provider:

```php
protected $policies = [
    Course::class => CoursePolicy::class,
    Lesson::class => LessonPolicy::class,
    Material::class => MaterialPolicy.class,
    // ... other policies
];
```

---

## API Controllers

### 1. Controller Structure

**Files**: `app/Http/Controllers/`

Each controller follows RESTful conventions:
- **AuthController**: Authentication endpoints
- **CourseController**: Course management
- **LessonController**: Lesson CRUD + approval
- **MaterialController**: File upload/download
- **QuestionController**: Q&A management
- **CommentController**: Discussion threads
- **RatingController**: Review system
- **ReportController**: Content moderation
- **EnrollmentController**: Course enrollment
- **AnalyticsController**: Admin analytics

### 2. Example: Course Controller

**File**: `app/Http/Controllers/CourseController.php`

```php
public function store(Request $request): JsonResponse
{
    Gate::authorize('create', Course::class);
    
    $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string',
    ]);
    
    $course = Course::create([
        'title' => $request->title,
        'description' => $request->description,
        'created_by' => $request->user()->id,
    ]);
    
    return response()->json([
        'course' => $course->load('creator:id,full_name'),
    ], 201);
}
```

### 3. Business Logic Implementation

Controllers implement key business rules:
- **Enrollment checks**: Users must be enrolled to access content
- **Approval workflow**: Teacher uploads require admin approval
- **One rating per user**: Prevents rating manipulation
- **Role-based access**: Different permissions per role

---

## Middleware Implementation

### 1. Authentication Middleware

Routes are protected using Sanctum's `auth:sanctum` middleware:

```php
Route::middleware('auth:sanctum')->group(function () {
    // All protected routes
});
```

### 2. Role-Based Protection

Admin-only routes use custom role middleware:

```php
Route::middleware('role:admin')->prefix('analytics')->group(function () {
    // Analytics endpoints
});
```

### 3. CORS Configuration

**File**: `bootstrap/app.php`

CORS middleware is configured for API access:

```php
$middleware->api(prepend: [
    \Illuminate\Http\Middleware\HandleCors::class,
]);
```

---

## File Structure

### 1. Project Organization

```
knowledge_bridge/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # API Controllers (11 files)
│   │   └── Middleware/      # Custom middleware
│   ├── Models/              # Eloquent models (9 files)
│   ├── Policies/            # Authorization policies (7 files)
│   └── Providers/           # Service providers
├── database/
│   ├── migrations/          # Database schema (12 files)
│   ├── factories/           # Model factories for testing
│   └── seeders/             # Database seeders
├── routes/
│   └── api.php             # API route definitions
└── config/
    └── sanctum.php         # Sanctum configuration
```

### 2. Route Organization

**File**: `routes/api.php`

Routes are logically grouped:
- **Authentication**: Register, login, logout
- **Courses**: CRUD operations
- **Lessons**: Content management with approval
- **Materials**: File operations
- **Questions & Comments**: Forum functionality
- **Ratings**: Review system
- **Reports**: Moderation tools
- **Enrollments**: Course access
- **Analytics**: Admin dashboard data

---

## Testing

### 1. Test Data Seeding

**File**: `database/seeders/DatabaseSeeder.php`

Comprehensive seeding strategy:
- **Test users**: One for each role with known credentials
- **Sample data**: Courses, lessons, questions, comments, ratings
- **Realistic relationships**: Proper enrollments and interactions

### 2. API Testing Script

**File**: `test_api.php`

Automated testing script that verifies:
- User registration and authentication
- Course creation and enrollment
- Authorization checks
- Error handling

### 3. Test Accounts

After seeding, these accounts are available:
- **Admin**: admin@knowledgebridge.com
- **Teacher**: teacher@knowledgebridge.com  
- **Student**: student@knowledgebridge.com
- **Graduate**: graduate@knowledgebridge.com

**Password for all accounts**: `password`

---

## Key Implementation Features

### 1. Security Measures

- **Token-based authentication** using Sanctum
- **Role-based authorization** with policies
- **Input validation** on all endpoints
- **Password hashing** with Laravel's Hash facade
- **CSRF protection** for web routes
- **SQL injection prevention** through Eloquent ORM

### 2. Business Logic

- **Enrollment requirement**: Users must enroll to access course content
- **Content approval workflow**: Teacher uploads require admin approval
- **One rating per user per lesson**: Prevents manipulation
- **Threaded comments**: Support for nested discussions
- **File upload/download**: Secure material management

### 3. Performance Considerations

- **Eager loading**: Relationships loaded efficiently
- **Database indexing**: Foreign keys and unique constraints
- **Query optimization**: Scoped queries for approved content
- **Pagination support**: Ready for large datasets

### 4. Error Handling

Consistent error response format:
```json
{
    "error": true,
    "message": "Error description",
    "code": 400
}
```

Common error codes:
- `401 Unauthorized`: Invalid or missing token
- `403 Forbidden`: Insufficient permissions
- `404 Not Found`: Resource doesn't exist
- `409 Conflict`: Duplicate rating or enrollment
- `422 Unprocessable Entity`: Validation errors

---

## Deployment Considerations

### 1. Environment Setup

- **Database**: Configure production database (MySQL/PostgreSQL)
- **File storage**: Set up proper file storage system
- **CORS**: Configure allowed origins for frontend
- **Token expiration**: Set appropriate token lifetimes

### 2. Security Hardening

- **HTTPS**: Enforce SSL in production
- **Rate limiting**: Implement API rate limits
- **Input sanitization**: Additional validation layers
- **Logging**: Comprehensive audit trails

### 3. Performance Optimization

- **Caching**: Implement Redis/Memcached
- **Database optimization**: Proper indexing strategy
- **File storage**: Use cloud storage (S3, etc.)
- **CDN**: Content delivery network for static assets

---

## API Usage Examples

### Authentication Flow

```bash
# Register
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"full_name":"John Doe","email":"john@example.com","password":"password123","password_confirmation":"password123"}'

# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"john@example.com","password":"password123"}'

# Use token in subsequent requests
curl -X GET http://localhost:8000/api/courses \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Course Management

```bash
# Create course (teacher/admin)
curl -X POST http://localhost:8000/api/courses \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Laravel Basics","description":"Learn Laravel fundamentals"}'

# Enroll in course
curl -X POST http://localhost:8000/api/courses/1/enroll \
  -H "Authorization: Bearer TOKEN"

# Get course lessons (enrolled users only)
curl -X GET http://localhost:8000/api/courses/1/lessons \
  -H "Authorization: Bearer TOKEN"
```

---

## Conclusion

The Knowledge Bridge project demonstrates a complete implementation of a modern academic platform API using Laravel 12 and Sanctum. The architecture emphasizes:

- **Security**: Comprehensive authentication and authorization
- **Scalability**: Well-structured models and relationships  
- **Maintainability**: Clean code organization and separation of concerns
- **Flexibility**: Role-based system supporting different user types
- **Robustness**: Proper error handling and validation

The implementation follows Laravel best practices and provides a solid foundation for an academic platform that can handle course management, content delivery, Q&A forums, and administrative functions.

---

## Quick Start Commands

```bash
# Clone and setup
git clone <repository-url>
cd backend_laravel
composer install

# Environment setup (Windows PowerShell)
Copy-Item .env.example .env
# OR for Linux/Mac
cp .env.example .env

# Generate application key
php artisan key:generate

# Database setup
# Create SQLite database file (Windows PowerShell)
New-Item -ItemType File -Path database/database.sqlite -Force
# OR for Linux/Mac
touch database/database.sqlite

# Run migrations and seed data
php artisan migrate --force
php artisan db:seed

# Start development server
php artisan serve

# Test the API
php test_api.php
```

The API will be available at `http://localhost:8000/api` with comprehensive documentation in `API_DOCUMENTATION.md`.

**Troubleshooting**: If you encounter database errors, ensure:
1. The `database/database.sqlite` file exists
2. The file has proper read/write permissions
3. The `DB_CONNECTION` in `.env` is set to `sqlite`
4. The `DB_DATABASE` path in `.env` is correct: `database/database.sqlite`


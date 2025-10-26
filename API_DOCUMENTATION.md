# Knowledge Bridge API Documentation

## Overview

Knowledge Bridge is a RESTful API built with Laravel 12 and Sanctum authentication. It provides a comprehensive academic platform with role-based access control, content management, Q&A forums, and analytics.

## Authentication

The API uses **Laravel Sanctum** for token-based authentication.

### Register
```http
POST /api/register
Content-Type: application/json

{
    "full_name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "student" // Optional: student, graduate (teacher/admin must be assigned by admin)
}
```

### Login
```http
POST /api/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "user": {
        "id": 1,
        "full_name": "John Doe",
        "email": "john@example.com",
        "role": "student"
    },
    "token": "1|abc123...",
    "token_type": "Bearer"
}
```

### Authenticated Requests
Include the token in all subsequent requests:
```http
Authorization: Bearer 1|abc123...
```

### Logout
```http
POST /api/logout
Authorization: Bearer {token}
```

## User Roles & Permissions

- **Student**: Can enroll in courses, ask questions, comment, rate lessons
- **Graduate**: Same as student
- **Teacher**: Can create courses, upload lessons/materials (subject to approval)
- **Admin**: Full access, can approve content, view analytics, moderate

## API Endpoints

### Courses

#### List All Courses
```http
GET /api/courses
Authorization: Bearer {token}
```

#### Create Course (Teachers/Admins only)
```http
POST /api/courses
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "Introduction to Laravel",
    "description": "Learn Laravel from basics to advanced concepts"
}
```

#### Get Course Details
```http
GET /api/courses/{id}
Authorization: Bearer {token}
```

#### Enroll in Course
```http
POST /api/courses/{id}/enroll
Authorization: Bearer {token}
```

#### Unenroll from Course
```http
DELETE /api/courses/{id}/unenroll
Authorization: Bearer {token}
```

### Lessons

#### Get Course Lessons (Enrolled users only)
```http
GET /api/courses/{id}/lessons
Authorization: Bearer {token}
```

#### Upload Lesson (Course teachers only)
```http
POST /api/courses/{id}/lessons
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "Laravel Basics",
    "content": "In this lesson we will cover..."
}
```

#### Approve/Reject Lesson (Admins only)
```http
PATCH /api/lessons/{id}/approve
Authorization: Bearer {token}
Content-Type: application/json

{
    "status": "approved" // or "rejected"
}
```

### Materials

#### Upload Material (Teachers only)
```http
POST /api/lessons/{id}/materials
Authorization: Bearer {token}
Content-Type: multipart/form-data

file: [binary file data]
```

#### Download Material (Enrolled users only)
```http
GET /api/materials/{id}/download
Authorization: Bearer {token}
```

### Questions & Answers

#### List Questions
```http
GET /api/questions
Authorization: Bearer {token}
```

#### Ask Question
```http
POST /api/questions
Authorization: Bearer {token}
Content-Type: application/json

{
    "question_text": "How do I implement authentication in Laravel?",
    "lesson_id": 1 // Optional: link to specific lesson
}
```

#### Answer Question (Comment on question)
```http
POST /api/questions/{id}/comments
Authorization: Bearer {token}
Content-Type: application/json

{
    "content": "You can use Laravel Sanctum for API authentication...",
    "parent_id": null // Optional: for reply to another comment
}
```

### Comments

#### Comment on Lesson (Enrolled users only)
```http
POST /api/lessons/{id}/comments
Authorization: Bearer {token}
Content-Type: application/json

{
    "content": "Great lesson! Very helpful.",
    "parent_id": null // Optional: for reply
}
```

### Ratings & Reviews

#### Rate Lesson (Enrolled students/graduates only, one per user)
```http
POST /api/lessons/{id}/ratings
Authorization: Bearer {token}
Content-Type: application/json

{
    "rating_value": 5, // 1-5 scale
    "review": "Excellent content and explanation!" // Optional
}
```

### Reports & Moderation

#### Report Content
```http
POST /api/reports
Authorization: Bearer {token}
Content-Type: application/json

{
    "target_type": "lesson", // lesson, comment, or question
    "target_id": 1,
    "reason": "Inappropriate content"
}
```

#### View Reports (Admins only)
```http
GET /api/reports
Authorization: Bearer {token}
```

#### Update Report Status (Admins only)
```http
PUT /api/reports/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "status": "resolved" // open, resolved, dismissed
}
```

### Analytics (Admins only)

#### Most Viewed Lessons
```http
GET /api/analytics/lessons/most-viewed
Authorization: Bearer {token}
```

#### Student Activity
```http
GET /api/analytics/students/activity
Authorization: Bearer {token}
```

#### Teacher Engagement
```http
GET /api/analytics/teachers/engagement
Authorization: Bearer {token}
```

#### Platform Statistics
```http
GET /api/analytics/platform/stats
Authorization: Bearer {token}
```

## Error Responses

All errors return JSON in this format:
```json
{
    "error": true,
    "message": "Error description",
    "code": 400
}
```

### Common Error Codes
- `401 Unauthorized`: Invalid or missing token
- `403 Forbidden`: Insufficient permissions or not enrolled
- `404 Not Found`: Resource doesn't exist
- `409 Conflict`: Duplicate rating or enrollment
- `422 Unprocessable Entity`: Validation errors

## Enrollment Requirements

Users must be enrolled in a course to:
- View course lessons
- Post questions/comments related to lessons
- Rate lessons
- Download materials

## Content Approval Workflow

1. Teachers upload lessons → Status: `pending`
2. Admin reviews → Status: `approved` or `rejected`
3. Only approved lessons are visible to students

## Database Schema

### Users
- `id`, `full_name`, `email`, `password`, `role`, `timestamps`

### Courses
- `id`, `title`, `description`, `created_by`, `timestamps`

### Lessons
- `id`, `course_id`, `title`, `content`, `uploaded_by`, `status`, `timestamps`

### Materials
- `id`, `lesson_id`, `file_name`, `file_path`, `uploaded_by`, `timestamps`

### Questions
- `id`, `user_id`, `lesson_id`, `question_text`, `timestamps`

### Comments
- `id`, `lesson_id`, `question_id`, `user_id`, `parent_id`, `content`, `timestamps`

### Ratings
- `id`, `lesson_id`, `user_id`, `rating_value`, `review`, `timestamps`

### Reports
- `id`, `user_id`, `target_type`, `target_id`, `reason`, `status`, `timestamps`

### Enrollments
- `id`, `user_id`, `course_id`, `timestamps`

## Test Data

Run `php artisan db:seed` to populate the database with test data:

- **Admin**: admin@knowledgebridge.com
- **Teacher**: teacher@knowledgebridge.com
- **Student**: student@knowledgebridge.com
- **Graduate**: graduate@knowledgebridge.com

Password for all test users: `password`

## Development Setup

1. Clone the repository
2. Run `composer install`
3. Copy `.env.example` to `.env`
4. Generate application key: `php artisan key:generate`
5. Configure database in `.env`
6. Run migrations: `php artisan migrate`
7. Seed database: `php artisan db:seed`
8. Start server: `php artisan serve`

The API will be available at `http://localhost:8000/api`

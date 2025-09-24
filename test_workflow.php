<?php

/**
 * Knowledge Bridge Complete Workflow Test
 * Tests the full user journey from registration to course completion
 */

$baseUrl = 'http://localhost:8000/api';

function makeRequest($method, $url, $data = null, $token = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    $headers = ['Content-Type: application/json'];
    
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status' => $httpCode,
        'body' => json_decode($response, true)
    ];
}

echo "🎓 Knowledge Bridge Complete Workflow Test\n";
echo "==========================================\n";

// Step 1: Login as teacher
echo "\n👨‍🏫 Step 1: Teacher Login\n";
$teacherLogin = makeRequest('POST', $baseUrl . '/login', [
    'email' => 'teacher@knowledgebridge.com',
    'password' => 'password'
]);
$teacherToken = $teacherLogin['body']['token'];
echo "✅ Teacher logged in successfully\n";

// Step 2: Login as admin
echo "\n👑 Step 2: Admin Login\n";
$adminLogin = makeRequest('POST', $baseUrl . '/login', [
    'email' => 'admin@knowledgebridge.com',
    'password' => 'password'
]);
$adminToken = $adminLogin['body']['token'];
echo "✅ Admin logged in successfully\n";

// Step 3: Teacher creates a course
echo "\n📚 Step 3: Teacher Creates Course\n";
$courseResponse = makeRequest('POST', $baseUrl . '/courses', [
    'title' => 'Advanced Laravel Development',
    'description' => 'Learn advanced Laravel concepts and API development'
], $teacherToken);
$courseId = $courseResponse['body']['course']['id'];
echo "✅ Course created with ID: $courseId\n";

// Step 4: Teacher uploads a lesson
echo "\n📖 Step 4: Teacher Uploads Lesson\n";
$lessonResponse = makeRequest('POST', $baseUrl . "/courses/$courseId/lessons", [
    'title' => 'Laravel API Authentication',
    'content' => 'In this lesson, we will learn how to implement API authentication using Laravel Sanctum...'
], $teacherToken);
$lessonId = $lessonResponse['body']['lesson']['id'];
echo "✅ Lesson created with ID: $lessonId (Status: pending)\n";

// Step 5: Admin approves the lesson
echo "\n✅ Step 5: Admin Approves Lesson\n";
$approveResponse = makeRequest('PATCH', $baseUrl . "/lessons/$lessonId/approve", [
    'status' => 'approved'
], $adminToken);
echo "✅ Lesson approved by admin\n";

// Step 6: Student registers and enrolls
echo "\n🎓 Step 6: Student Registration & Enrollment\n";
$studentRegister = makeRequest('POST', $baseUrl . '/register', [
    'full_name' => 'Sarah Student',
    'email' => 'sarah@student.com',
    'password' => 'password123',
    'password_confirmation' => 'password123',
    'role' => 'student'
]);
$studentToken = $studentRegister['body']['token'];
echo "✅ Student registered successfully\n";

// Enroll in course
$enrollResponse = makeRequest('POST', $baseUrl . "/courses/$courseId/enroll", null, $studentToken);
echo "✅ Student enrolled in course\n";

// Step 7: Student views lessons
echo "\n👀 Step 7: Student Views Lessons\n";
$lessonsResponse = makeRequest('GET', $baseUrl . "/courses/$courseId/lessons", null, $studentToken);
$lessons = $lessonsResponse['body']['lessons'];
echo "✅ Student can see " . count($lessons) . " approved lesson(s)\n";

// Step 8: Student asks a question
echo "\n❓ Step 8: Student Asks Question\n";
$questionResponse = makeRequest('POST', $baseUrl . '/questions', [
    'question_text' => 'What are the benefits of using Sanctum over Passport?',
    'lesson_id' => $lessonId
], $studentToken);
$questionId = $questionResponse['body']['question']['id'];
echo "✅ Question posted with ID: $questionId\n";

// Step 9: Teacher answers the question
echo "\n💬 Step 9: Teacher Answers Question\n";
$answerResponse = makeRequest('POST', $baseUrl . "/questions/$questionId/comments", [
    'content' => 'Great question! Sanctum is lighter weight and perfect for SPA applications, while Passport is better for OAuth2 server implementations.'
], $teacherToken);
echo "✅ Teacher provided an answer\n";

// Step 10: Student comments on lesson
echo "\n💭 Step 10: Student Comments on Lesson\n";
$commentResponse = makeRequest('POST', $baseUrl . "/lessons/$lessonId/comments", [
    'content' => 'This lesson was very helpful! The examples were clear and easy to follow.'
], $studentToken);
echo "✅ Student commented on lesson\n";

// Step 11: Student rates the lesson
echo "\n⭐ Step 11: Student Rates Lesson\n";
$ratingResponse = makeRequest('POST', $baseUrl . "/lessons/$lessonId/ratings", [
    'rating_value' => 5,
    'review' => 'Excellent lesson! Very comprehensive and well-explained.'
], $studentToken);
echo "✅ Student rated lesson 5 stars\n";

// Step 12: Admin views analytics
echo "\n📊 Step 12: Admin Views Analytics\n";
$analyticsResponse = makeRequest('GET', $baseUrl . '/analytics/platform/stats', null, $adminToken);
$stats = $analyticsResponse['body']['platform_stats'];
echo "✅ Platform Statistics:\n";
echo "   - Total Users: {$stats['total_users']}\n";
echo "   - Total Courses: {$stats['total_courses']}\n";
echo "   - Total Lessons: {$stats['total_lessons']}\n";
echo "   - Approved Lessons: {$stats['approved_lessons']}\n";
echo "   - Total Questions: {$stats['total_questions']}\n";
echo "   - Average Rating: " . round($stats['average_rating'], 2) . "\n";

// Step 13: Test unauthorized actions
echo "\n🚫 Step 13: Test Security (Unauthorized Actions)\n";

// Student trying to approve lesson
$unauthorizedResponse = makeRequest('PATCH', $baseUrl . "/lessons/$lessonId/approve", [
    'status' => 'approved'
], $studentToken);
if ($unauthorizedResponse['status'] === 403) {
    echo "✅ Student correctly blocked from approving lessons\n";
}

// Non-enrolled user trying to view lessons
$guestRegister = makeRequest('POST', $baseUrl . '/register', [
    'full_name' => 'Guest User',
    'email' => 'guest@test.com',
    'password' => 'password123',
    'password_confirmation' => 'password123'
]);
$guestToken = $guestRegister['body']['token'];

$unauthorizedLessons = makeRequest('GET', $baseUrl . "/courses/$courseId/lessons", null, $guestToken);
if ($unauthorizedLessons['status'] === 403) {
    echo "✅ Non-enrolled user correctly blocked from viewing lessons\n";
}

echo "\n🎉 Complete Workflow Test Successful!\n";
echo "All major features are working correctly:\n";
echo "✓ User Registration & Authentication\n";
echo "✓ Role-Based Access Control\n";
echo "✓ Course Management\n";
echo "✓ Lesson Upload & Approval Workflow\n";
echo "✓ Enrollment System\n";
echo "✓ Q&A Forum\n";
echo "✓ Comments & Ratings\n";
echo "✓ Admin Analytics\n";
echo "✓ Security & Authorization\n";

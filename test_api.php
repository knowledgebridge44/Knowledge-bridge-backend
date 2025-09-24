<?php

/**
 * Knowledge Bridge API Test Script
 * This script tests the main API endpoints to verify functionality
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

function testEndpoint($description, $method, $endpoint, $data = null, $token = null) {
    global $baseUrl;
    echo "\n🧪 Testing: $description\n";
    echo "   $method $endpoint\n";
    
    $response = makeRequest($method, $baseUrl . $endpoint, $data, $token);
    
    echo "   Status: {$response['status']}\n";
    
    if ($response['status'] >= 200 && $response['status'] < 300) {
        echo "   ✅ SUCCESS\n";
    } else {
        echo "   ❌ FAILED\n";
        if (isset($response['body']['message'])) {
            echo "   Error: {$response['body']['message']}\n";
        }
    }
    
    return $response;
}

echo "🚀 Knowledge Bridge API Testing\n";
echo "================================\n";

// Test 1: Register a new student
$registerResponse = testEndpoint(
    'Register new student',
    'POST',
    '/register',
    [
        'full_name' => 'Test Student',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'student'
    ]
);

$studentToken = $registerResponse['body']['token'] ?? null;

// Test 2: Login with existing user
$loginResponse = testEndpoint(
    'Login existing user (admin)',
    'POST',
    '/login',
    [
        'email' => 'admin@knowledgebridge.com',
        'password' => 'password'
    ]
);

$adminToken = $loginResponse['body']['token'] ?? null;

// Test 3: Get user profile
if ($studentToken) {
    testEndpoint(
        'Get user profile',
        'GET',
        '/me',
        null,
        $studentToken
    );
}

// Test 4: Get courses
if ($studentToken) {
    testEndpoint(
        'Get all courses',
        'GET',
        '/courses',
        null,
        $studentToken
    );
}

// Test 5: Create a course (as admin)
if ($adminToken) {
    $courseResponse = testEndpoint(
        'Create new course (admin)',
        'POST',
        '/courses',
        [
            'title' => 'API Testing Course',
            'description' => 'A course created via API testing'
        ],
        $adminToken
    );
    
    $courseId = $courseResponse['body']['course']['id'] ?? null;
    
    // Test 6: Enroll student in course
    if ($courseId && $studentToken) {
        testEndpoint(
            'Enroll student in course',
            'POST',
            "/courses/$courseId/enroll",
            null,
            $studentToken
        );
        
        // Test 7: Get course lessons (should be empty initially)
        testEndpoint(
            'Get course lessons',
            'GET',
            "/courses/$courseId/lessons",
            null,
            $studentToken
        );
    }
}

// Test 8: Get questions
if ($studentToken) {
    testEndpoint(
        'Get all questions',
        'GET',
        '/questions',
        null,
        $studentToken
    );
}

// Test 9: Ask a question
if ($studentToken) {
    testEndpoint(
        'Ask a question',
        'POST',
        '/questions',
        [
            'question_text' => 'How do I use the Knowledge Bridge API?'
        ],
        $studentToken
    );
}

// Test 10: Try unauthorized action (student creating course)
if ($studentToken) {
    testEndpoint(
        'Try unauthorized action (student creating course)',
        'POST',
        '/courses',
        [
            'title' => 'Unauthorized Course',
            'description' => 'This should fail'
        ],
        $studentToken
    );
}

// Test 11: Logout
if ($studentToken) {
    testEndpoint(
        'Logout student',
        'POST',
        '/logout',
        null,
        $studentToken
    );
}

echo "\n🎉 API Testing Complete!\n";
echo "Check the results above to verify all endpoints are working correctly.\n";

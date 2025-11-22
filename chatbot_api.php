<?php
/**
 * Chatbot API Handler
 * Handles messages from the frontend and communicates with Google Gemini API
 * 
 * Usage: POST /php/chatbot_api.php
 * Parameters: {"message": "user message here"}
 */
error_log('Using Gemini API URL: ' . $apiUrl);
error_log('Chatbot API loaded at ' . date('Y-m-d H:i:s'));

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Your Google Gemini API Key
$GEMINI_API_KEY = 'AIzaSyCvi1lCpjwzREtxLn-GOtggZUwUzb7FqtU';

// Get the message from the request
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['message']) || empty(trim($input['message']))) {
    http_response_code(400);
    echo json_encode(['error' => 'Message is required']);
    exit;
}

$userMessage = trim($input['message']);

// Validate API key is set
if ($GEMINI_API_KEY === 'YOUR_GEMINI_API_KEY_HERE') {
    http_response_code(500);
    echo json_encode(['error' => 'API key not configured. Please set your Google Gemini API key.']);
    exit;
}

// ✅ Updated model and endpoint
$apiUrl = 'https://generativelanguage.googleapis.com/v1/models/gemini-1.5-pro:generateContent?key=' . $GEMINI_API_KEY;

// System prompt for the chatbot
$systemPrompt = "You are a helpful assistant for a Vietnamese mobile phone e-commerce website. "
    . "You should help customers with product inquiries, orders, shipping, and general questions. "
    . "Be friendly, professional, and concise. Always respond in the same language as the user.";

$requestBody = [
    'contents' => [
        [
            'parts' => [
                ['text' => $systemPrompt . "\n\nUser: " . $userMessage]
            ]
        ]
    ],
    'generationConfig' => [
        'temperature' => 0.7,
        'topP' => 0.95,
        'topK' => 40,
        'maxOutputTokens' => 500,
    ]
];

// Make the API request
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Handle API errors
if ($error) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to connect to Gemini API: ' . $error]);
    exit;
}

if ($httpCode !== 200) {
    http_response_code($httpCode);
    $errorResponse = json_decode($response, true);
    $errorMsg = $errorResponse['error']['message'] ?? 'Unknown error';
    
    error_log('Gemini API Error: ' . $response);
    
    echo json_encode(['error' => 'Gemini API error: ' . $errorMsg]);
    exit;
}

// Parse the response
$apiResponse = json_decode($response, true);

if (!isset($apiResponse['candidates'][0]['content']['parts'][0]['text'])) {
    http_response_code(500);
    echo json_encode(['error' => 'Unexpected response format from Gemini API']);
    exit;
}

$botMessage = $apiResponse['candidates'][0]['content']['parts'][0]['text'];

// Return the bot's response
http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => $botMessage,
    'timestamp' => date('Y-m-d H:i:s')
]);
?>
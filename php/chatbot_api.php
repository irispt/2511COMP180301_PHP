<?php
/**
 * Chatbot API Handler
 * Handles messages from the frontend and communicates with Google Gemini API
 * * Usage: POST /php/chatbot_api.php
 * Parameters: {"message": "user message here"}
 */

// Load database classes
require_once(__DIR__ . '/../BackEnd/ConnectionDB/DB_classes.php');

// Logging function
function logDebug($message, $data = null) {
    $logFile = __DIR__ . '/../logs/chatbot_debug.log';
    $logDir = dirname($logFile);
    
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message";
    
    if ($data !== null) {
        $logMessage .= "\n" . print_r($data, true);
    }
    
    $logMessage .= "\n" . str_repeat('-', 80) . "\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

logDebug('=== Chatbot API Request Started ===');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    logDebug('OPTIONS request received');
    http_response_code(200);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logDebug('Invalid request method: ' . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

logDebug('Request method: POST');

// Your Google Gemini API Key
// IMPORTANT: Replace this with your actual API key from makersuite.google.com/app/apikey
// For production, use environment variables or a config file
$GEMINI_API_KEY = 'AIzaSyCZ1sd5HJscKh-yK7heiHSYh960pB8Lkxo';

logDebug('API Key configured: ' . (empty($GEMINI_API_KEY) ? 'NO' : 'YES'));

// Get the message from the request
$rawInput = file_get_contents('php://input');
logDebug('Raw input received', $rawInput);

$input = json_decode($rawInput, true);
logDebug('Decoded input', $input);

if (!isset($input['message']) || empty(trim($input['message']))) {
    logDebug('ERROR: Message is missing or empty');
    http_response_code(400);
    echo json_encode(['error' => 'Message is required']);
    exit;
}

$userMessage = trim($input['message']);
logDebug('User message: ' . $userMessage);

// Validate API key is set
if ($GEMINI_API_KEY === 'YOUR_GEMINI_API_KEY_HERE') {
    logDebug('ERROR: API key not configured');
    http_response_code(500);
    echo json_encode(['error' => 'API key not configured. Please set your Google Gemini API key.']);
    exit;
}

// --- START FIX ---

// Use gemini-2.0-flash which is available in v1 API
$MODEL_NAME = 'gemini-2.0-flash';
$apiUrl = 'https://generativelanguage.googleapis.com/v1/models/' . $MODEL_NAME . ':generateContent?key=' . $GEMINI_API_KEY;

logDebug('API URL: ' . $apiUrl);
logDebug('Model: ' . $MODEL_NAME);

// --- END FIX ---

// System prompt for the chatbot (customize this for your website)
$systemPrompt = "Bạn là trợ lý AI thông minh cho website bán điện thoại di động tại Việt Nam. "
    . "Nhiệm vụ của bạn:\n"
    . "1. Tư vấn sản phẩm điện thoại dựa trên thông tin được cung cấp\n"
    . "2. Giải đáp thắc mắc về đơn hàng, giao hàng, bảo hành\n"
    . "3. Hỗ trợ khách hàng một cách thân thiện, chuyên nghiệp\n"
    . "4. Luôn trả lời bằng tiếng Việt\n"
    . "5. Nếu khách hỏi về sản phẩm cụ thể, hãy dựa vào dữ liệu sản phẩm có sẵn để tư vấn\n\n";

// Get product information from database to enhance chatbot knowledge
try {
    $spBUS = new SanPhamBUS();
    $products = $spBUS->select_all();
    
    if ($products && count($products) > 0) {
        $systemPrompt .= "Dưới đây là danh sách sản phẩm hiện có trên website:\n\n";
        
        $productCount = 0;
        foreach ($products as $sp) {
            if ($sp['TrangThai'] == 1 && $sp['SoLuong'] > 0 && $productCount < 20) {
                // Get category info
                $loaiSP = (new LoaiSanPhamBUS())->select_by_id('*', $sp['MaLSP']);
                $tenLoai = $loaiSP ? $loaiSP['TenLSP'] : 'Không rõ';
                
                $systemPrompt .= "- " . $sp['TenSP'] . "\n";
                $systemPrompt .= "  + Giá: " . number_format($sp['DonGia'], 0, ',', '.') . "đ\n";
                $systemPrompt .= "  + Loại: " . $tenLoai . "\n";
                $systemPrompt .= "  + Còn hàng: " . $sp['SoLuong'] . " sản phẩm\n";
                if (!empty($sp['MoTa'])) {
                    $systemPrompt .= "  + Mô tả: " . substr($sp['MoTa'], 0, 150) . "...\n";
                }
                $systemPrompt .= "\n";
                $productCount++;
            }
        }
        
        $systemPrompt .= "\nHãy sử dụng thông tin trên để tư vấn chính xác cho khách hàng.\n";
    }
} catch (Exception $e) {
    logDebug('Error loading products: ' . $e->getMessage());
}

logDebug('System prompt prepared', substr($systemPrompt, 0, 500) . '...');

// Prepare request body - Updated format for Gemini API
$requestBody = [
    'contents' => [
        [
            'role' => 'user',
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

logDebug('Request body prepared', $requestBody);

// Make the API request
logDebug('Initiating cURL request...');

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
$curlInfo = curl_getinfo($ch);
curl_close($ch);

logDebug('cURL completed');
logDebug('HTTP Code: ' . $httpCode);
logDebug('cURL Error: ' . ($error ?: 'None'));
logDebug('cURL Info', $curlInfo);
logDebug('API Response', $response);

// Handle API errors
if ($error) {
    logDebug('CURL ERROR: ' . $error);
    http_response_code(500);
    echo json_encode(['error' => 'Failed to connect to Gemini API: ' . $error]);
    exit;
}

if ($httpCode !== 200) {
    logDebug('API ERROR - HTTP Code: ' . $httpCode);
    $errorResponse = json_decode($response, true);
    logDebug('Error response decoded', $errorResponse);
    
    $errorMsg = 'Unknown error';
    if (isset($errorResponse['error']['message'])) {
        $errorMsg = $errorResponse['error']['message'];
    }
    
    logDebug('Final error message: ' . $errorMsg);
    http_response_code($httpCode);
    echo json_encode(['error' => 'Gemini API error: ' . $errorMsg, 'details' => $errorResponse]);
    exit;
}

// Parse the response
$apiResponse = json_decode($response, true);
logDebug('API response decoded', $apiResponse);

if (!isset($apiResponse['candidates'][0]['content']['parts'][0]['text'])) {
    logDebug('ERROR: Unexpected response format');
    logDebug('Response structure', $apiResponse);
    http_response_code(500);
    echo json_encode([
        'error' => 'Unexpected response format from Gemini API',
        'response' => $apiResponse
    ]);
    exit;
}

$botMessage = $apiResponse['candidates'][0]['content']['parts'][0]['text'];
logDebug('Bot message extracted: ' . $botMessage);

// Return the bot's response
http_response_code(200);
$successResponse = [
    'success' => true,
    'message' => $botMessage,
    'timestamp' => date('Y-m-d H:i:s')
];

logDebug('SUCCESS - Sending response', $successResponse);
logDebug('=== Chatbot API Request Completed Successfully ===');

echo json_encode($successResponse);
?>
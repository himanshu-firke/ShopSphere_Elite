<?php

// Simple test script to verify streamlined API endpoints
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "=== Streamlined API Test ===\n\n";

try {
    // Test 1: Health Check
    echo "1. Testing Health Endpoint...\n";
    $request = Illuminate\Http\Request::create('/api/streamlined/health', 'GET');
    $response = $kernel->handle($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Response: " . $response->getContent() . "\n\n";

    // Test 2: Categories Endpoint
    echo "2. Testing Categories Endpoint...\n";
    $request = Illuminate\Http\Request::create('/api/streamlined/categories', 'GET');
    $response = $kernel->handle($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Response: " . $response->getContent() . "\n\n";

    // Test 3: Products Endpoint
    echo "3. Testing Products Endpoint...\n";
    $request = Illuminate\Http\Request::create('/api/streamlined/products', 'GET');
    $response = $kernel->handle($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Response: " . $response->getContent() . "\n\n";

    // Test 4: Data Flow Test
    echo "4. Testing Data Flow Endpoint...\n";
    $request = Illuminate\Http\Request::create('/api/streamlined/test-data-flow', 'GET');
    $response = $kernel->handle($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Response: " . $response->getContent() . "\n\n";

    echo "=== All API Tests Completed ===\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

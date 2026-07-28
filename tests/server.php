<?php

// Test app served by PHP's built-in server. Each endpoint returns a JSON
// snapshot of what Leaf\Http\Request parsed from the real request, so the
// suite can assert against actual SAPI behavior instead of fakes.

require __DIR__ . '/../vendor/autoload.php';

use Leaf\Http\Headers;
use Leaf\Http\Request;

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

header('Content-Type: application/json');

if ($path === '/body') {
    echo json_encode([
        'method' => Request::getMethod(),
        'body' => Request::body(false),
        'input' => Request::input(false),
    ]);

    return;
}

if ($path === '/get') {
    echo json_encode([
        'single' => Request::get('name', false),
        'multiple' => Request::get(['name', 'missing'], false),
        'params' => Request::params('missing', 'fallback'),
        'try' => Request::try(['name', 'missing'], false),
    ]);

    return;
}

if ($path === '/headers') {
    echo json_encode([
        'all' => Headers::all(),
        'single' => Headers::get('X-Custom'),
        'lowercase' => Headers::get('x-custom'),
        'has' => Headers::has('X-Custom'),
        'hasValue' => Headers::has('custom-value'),
        'contentLength' => Request::getContentLength(),
        'userAgent' => Request::getUserAgent(),
    ]);

    return;
}

if ($path === '/url') {
    echo json_encode([
        'host' => Request::getHost(),
        'port' => Request::getPort(),
        'scheme' => Request::getScheme(),
        'url' => Request::getUrl(),
        'fullUrl' => Request::getFullUrl(),
        'path' => Request::getPath(),
        'query' => Request::getQueryString(),
        'ip' => Request::getIp(),
    ]);

    return;
}

if ($path === '/override') {
    echo json_encode([
        'method' => Request::getMethod(),
        'original' => Request::getOriginalMethod(),
    ]);

    return;
}

echo json_encode(['path' => $path]);

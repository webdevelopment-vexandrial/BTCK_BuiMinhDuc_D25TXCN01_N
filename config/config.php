<?php

define('APP_NAME', 'Cua hang Bui Minh Duc');
define('APP_DEBUG', true); // true: hiện lỗi chi tiết (chỉ dùng khi phát triển)

$projectRoot = realpath(__DIR__ . '/..');
$documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
$baseUrl = '/';

if ($projectRoot !== false && $documentRoot !== false) {
    $documentRoot = rtrim($documentRoot, '\\/');
    if (strncasecmp($projectRoot, $documentRoot, strlen($documentRoot)) === 0) {
        $relativePath = substr($projectRoot, strlen($documentRoot));
        if ($relativePath === '' || $relativePath === false) {
            $baseUrl = '/';
        } elseif ($relativePath[0] === '\\' || $relativePath[0] === '/') {
            $baseUrl = '/' . trim(str_replace('\\', '/', $relativePath), '/') . '/';
        }
    }
}

define('BASE_URL', $baseUrl);
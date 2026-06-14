<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
$file = __DIR__ . '/data/site.json';
if (!is_file($file)) {
    http_response_code(404);
    echo json_encode(['error' => 'content file not found'], JSON_UNESCAPED_UNICODE);
    exit;
}
$json = file_get_contents($file);
if ($json === false) {
    http_response_code(500);
    echo json_encode(['error' => 'cannot read content'], JSON_UNESCAPED_UNICODE);
    exit;
}
echo $json;

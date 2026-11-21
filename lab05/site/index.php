<?php

declare(strict_types=1);

require_once __DIR__ . '/modules/database.php';
require_once __DIR__ . '/modules/page.php';

$config = require __DIR__ . '/config.php';
$rawId = $_GET['page'] ?? '1';
$pageId = filter_var($rawId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($pageId === false) {
    http_response_code(400);
    exit('Invalid page ID');
}

try {
    $db = new Database($config['db']['path']);
    $data = $db->Read('page', $pageId);
    if ($data === null) {
        http_response_code(404);
        exit('Page not found');
    }
    $page = new Page(__DIR__ . '/templates/index.tpl');
    echo $page->Render($data);
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    echo 'Internal server error';
}

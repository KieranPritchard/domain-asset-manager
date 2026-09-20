// router.php (project root)
<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false; // serve the actual file (css/js/images)
}

require __DIR__ . '/public/index.php';
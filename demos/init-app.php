<?php

declare(strict_types=1);

namespace Atk4\Login\Demos;

date_default_timezone_set('UTC');

require_once __DIR__ . '/init-autoloader.php';

$app = new App();

try {
    /** @var \Atk4\Data\Persistence\Sql $db */
    require_once __DIR__ . '/init-db.php';
    $app->db = $db;
    unset($db);
} catch (\Throwable $e) {
    throw new \Atk4\Ui\Exception('Database error: ' . $e->getMessage());
}

$app->invokeInit();

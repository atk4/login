<?php

declare(strict_types=1);

namespace Atk4\Login\Demos;

use Atk4\Data\Persistence;
use Atk4\Ui\Behat\CoverageUtil;
use Atk4\Ui\Exception;

date_default_timezone_set('UTC');

require_once __DIR__ . '/init-autoloader.php';

// collect coverage for HTTP tests 1/2
$coverageSaveFx = null;
if (is_dir(__DIR__ . '/../coverage') && !CoverageUtil::isCalledFromPhpunit()) {
    CoverageUtil::startFromPhpunitConfig(__DIR__ . '/..');
    $coverageSaveFx = static function (): void {
        CoverageUtil::saveData(__DIR__ . '/../coverage');
    };
}

$app = new App();

// collect coverage for HTTP tests 2/2
if ($coverageSaveFx !== null) {
    $app->onHook(App::HOOK_BEFORE_EXIT, $coverageSaveFx);
}
unset($coverageSaveFx);

try {
    /** @var Persistence\Sql $db */
    require_once __DIR__ . '/init-db.php';
    $app->db = $db;
    unset($db);
} catch (\Throwable $e) {
    throw new Exception('Database error: ' . $e->getMessage());
}

$app->init();

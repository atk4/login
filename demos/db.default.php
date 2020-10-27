<?php

declare(strict_types=1);

namespace atk4\login\demo;

require 'src/App.php';
require 'src/MigratorConsole.php';

if (file_exists('config.php')) {
    $config = require 'config.php';
    $db = new \atk4\data\Persistence\Sql($config['dsn']);
} else {
    $sqliteFile = __DIR__ . '/_demo-data/db.sqlite';
    if (!file_exists($sqliteFile)) {
        throw new \Exception('Sqlite database does not exist, create it first.');
    }
    $db = new \atk4\data\Persistence\Sql('sqlite:' . $sqliteFile);
    unset($sqliteFile);
}

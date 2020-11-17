<?php

declare(strict_types=1);

namespace atk4\login\demo;

require 'src/App.php';
require 'src/MigratorConsole.php';

// creates config file if possible
$sqliteFile = __DIR__ . '/_demo-data/db.sqlite';
if (!file_exists('config.php') && file_exists($sqliteFile)) {
    $string_config = "<?php\n\n" .
        "declare(strict_types=1);\n\n" .
        "return [\n" .
        "    'dsn'=>'sqlite:" . $sqliteFile . "'\n" .
        "];\n";
    file_put_contents('config.php', $string_config);
}

// connect database if possible
if (!file_exists('config.php')) {
    throw new \Exception('Sqlite database or config.php file does not exist.');
}

$config = require 'config.php';
$db = new \atk4\data\Persistence\Sql($config['dsn']);

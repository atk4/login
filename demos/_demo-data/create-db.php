<?php

declare(strict_types=1);

namespace atk4\login\demo;

require '../../vendor/autoload.php';

// CREATE TABLES AND POPULATE DATA ------------------------
$config = require '../config.php';
$data = file_get_contents('dump.sql');

$c = \atk4\dsql\Connection::connect($config['dns']);
$c->expr($data)->execute();

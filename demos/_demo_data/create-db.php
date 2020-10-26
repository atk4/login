<?php

declare(strict_types=1);

namespace atk4\login\demo;

include '../vendor/autoload.php';

// CREATE TABLES AND POPULATE DATA ------------------------
$config = require 'config.php';
$data = file_get_contents('dump.sql');

$c = new \atk4\dsql\Connection::connect($config['dns']);
$c->expr($data)->execute();

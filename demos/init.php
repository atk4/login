<?php

declare(strict_types=1);

namespace atk4\login\demo;

include '../vendor/autoload.php';

// init App and DB
$app = new App();
$app->dbFile = __DIR__ . '/data/db.sqlite';
$app->db = new \atk4\data\Persistence\Sql('sqlite:' . $app->dbFile);
//$app->db->setApp($app);

<?php

declare(strict_types=1);

namespace atk4\login\demo;

require '../../vendor/autoload.php';

// CREATE TABLES AND POPULATE DATA ------------------------
$config = require '../config.php';
$data = file_get_contents('sqlite-dump.sql');

$c = \atk4\dsql\Connection::connect($config['dsn']);

foreach (preg_split('~;\s*(\n\s*|$)~', $data) as $query) {
    echo $query;
    $pdo = $c->expr($query)->execute();
}

var_dump($c->expr('SELECT name FROM sqlite_master WHERE type = "table"')->get());
var_dump($c->expr('select * from login_user')->get());
var_dump($c->expr('select "login_user"."id","login_user"."name","login_user"."email","login_user"."password","login_user"."role_id",(select "name" from "login_role" "r" where "id" = "login_user"."role_id") "role" from "login_user" where "login_user"."email" = \'admin\' limit 0, 1')->get());

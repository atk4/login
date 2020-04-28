<?php

namespace atk4\login\demo;

include '../vendor/autoload.php';
include 'db.php';

use atk4\login\RoleAdmin;
use atk4\login\Model\Role;

$app = new \atk4\login\demo\App('admin');

// USERS --------------------------------------------------
$app->add('Header')->set('Roles');
$app->add(new RoleAdmin())
    ->setModel(new Role($app->db));

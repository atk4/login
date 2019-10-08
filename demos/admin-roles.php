<?php
namespace atk4\login\demo;

include '../vendor/autoload.php';
include 'db.php';

use atk4\login\RoleAdmin;
use atk4\login\Model\Role;

$app = new \atk4\login\demo\App('admin');

// ROLES --------------------------------------------------
$app->add('Header')->set('Roles');
$m = $app->add('CRUD')->setModel(new Role($app->db));

// ROLE PERMISSIONS ---------------------------------------
$app->add('Header')->set('Role Permissions');
$app->add('CRUD')->setModel($m->refModel('AccessRules'));

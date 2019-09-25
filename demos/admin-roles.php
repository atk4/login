<?php
namespace atk4\login\demo;

include '../vendor/autoload.php';
include 'db.php';

$app = new \atk4\login\demo\App('admin');

// ROLES --------------------------------------------------
$app->add('Header')->set('Roles');
$app->add('CRUD')->setModel(new \atk4\login\Model\Role($app->db));

// ROLE PERMISSIONS ---------------------------------------
$app->add('Header')->set('Role Permissions');
$app->add('CRUD')->setModel(new \atk4\login\Model\AccessRule($app->db));

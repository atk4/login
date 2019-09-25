<?php
namespace atk4\login\demo;

include '../vendor/autoload.php';
include 'db.php';

$app = new \atk4\login\demo\App('admin');

// USERS --------------------------------------------------
$app->add('Header')->set('Users');
$app->add(new \atk4\login\UserAdmin())
    ->setModel(new \atk4\login\Model\User($app->db));

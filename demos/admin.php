<?php
namespace atk4\login\demo;

include '../vendor/autoload.php';
include 'db.php';

$app = new \atk4\login\demo\App('admin');

//$app->add('Columns');
$app->add('CRUD')->setModel(new \atk4\login\Model\Role($app->db));

$app->add(new \atk4\login\UserAdmin())
    ->setModel(new \atk4\login\Model\User($app->db));

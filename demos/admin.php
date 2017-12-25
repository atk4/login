<?php
include '../vendor/autoload.php';
include 'db.php';

$app = new App('admin');
$app->add(new \atk4\login\UserAdmin())
    ->setModel(new \atk4\login\Model\User($app->db));

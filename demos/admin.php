<?php
include '../vendor/autoload.php';
include 'db.php';
$app = new \atk4\ui\App();
$app->initLayout('Admin');
$app->add('CRUD')->setModel(new \atk4\login\Model\User($db));

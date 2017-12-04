<?php
include '../vendor/autoload.php';
include 'db.php';
$app = new \atk4\ui\App();
$app->initLayout('Centered');


$app->add(new \atk4\login\UserAdmin())
    ->setModel(new \atk4\login\Model\User($db));

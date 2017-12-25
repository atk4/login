<?php

include '../vendor/autoload.php';
include 'db.php';

$app = new App(false);
 
$app->add(['Header', 'Welcome to Auth Add-on demo app']);
$app->add(['Button', 'Run migration wizard'])->link(['wizard']);
$app->add(['Button', 'Log-in'])->link(['login']);
$app->add(['Button', 'Register'])->link(['register']);
$app->add(['Button', 'Admin'])->link(['admin']);

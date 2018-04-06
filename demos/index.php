<?php

include '../vendor/autoload.php';
include 'db.php';

$app = new App(false);
 
$app->add(['Header', 'Welcome to Auth Add-on demo app']);
$app->add(['Button', 'Run migration wizard', 'icon'=>'gift'])->link(['wizard']);

$app->add(['ui'=>'divider']);
$app->add(['Button', 'Log-in', 'icon'=>'sign in'])->link(['login']);
$app->add(['Button', 'Register', 'icon'=>'edit'])->link(['register']);
$app->add(['Button', 'Dashboard', 'icon'=>'dashboard'])->link(['dashboard']);

$app->add(['ui'=>'divider']);
$app->add(['Button', 'Admin', 'icon'=>'lock open'])->link(['admin']);
$app->add(['Button', 'Admin with Auth', 'icon'=>'lock'])->link(['admin_locked']);

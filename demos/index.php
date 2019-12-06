<?php
namespace atk4\login\demo;

require '../vendor/autoload.php';
require 'db.php';

$app = new \atk4\login\demo\App('centered', false, true);

$app->add(['Header', 'Welcome to Auth Add-on demo app']);
$app->add(['Button', 'Run migration wizard', 'icon'=>'gift'])->link(['wizard']);

$app->add(['ui'=>'divider']);
$app->add(['Button', 'Log-in', 'icon'=>'sign in'])->link(['login']);
$app->add(['Button', 'Register', 'icon'=>'edit'])->link(['register']);
$app->add(['Button', 'Dashboard', 'icon'=>'dashboard'])->link(['dashboard']);

$app->add(['ui'=>'divider']);
$app->add(['Button', 'Admin', 'icon'=>'lock open'])->link(['admin-users']);

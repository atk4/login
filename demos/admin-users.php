<?php

namespace atk4\login\demo;

include '../vendor/autoload.php';
include 'db.php';

use atk4\login\UserAdmin;
use atk4\login\Model\User;

$app = new \atk4\login\demo\App('admin');

// USERS --------------------------------------------------
\atk4\ui\Header::addTo($app)->set('Users');
$app->add(new UserAdmin())
    ->setModel(new User($app->db));

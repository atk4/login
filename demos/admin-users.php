<?php

declare(strict_types=1);

namespace atk4\login\demo;

include '../vendor/autoload.php';
include 'db.php';

use atk4\login\Model\User;
use atk4\login\UserAdmin;
use atk4\ui\Header;

$app = new \atk4\login\demo\App('admin');

// USERS --------------------------------------------------
Header::addTo($app)->set('Users');
$app->add(new UserAdmin())
    ->setModel(new User($app->db));

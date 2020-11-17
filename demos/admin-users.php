<?php

declare(strict_types=1);

namespace atk4\login\demo;

use atk4\login\Model\User;
use atk4\login\UserAdmin;
use atk4\ui\Header;

include 'init.php';

Header::addTo($app)->set('Users');
$app->add(new UserAdmin())
    ->setModel(new User($app->db));

<?php

declare(strict_types=1);

namespace atk4\login\demo;

include '../vendor/autoload.php';
include 'db.php';

use atk4\login\Model\Role;
use atk4\login\RoleAdmin;
use atk4\ui\Header;

$app = new \atk4\login\demo\App('admin');

// USERS --------------------------------------------------
Header::addTo($app)->set('Roles');
RoleAdmin::addTo($app)->setModel(new Role($app->db));

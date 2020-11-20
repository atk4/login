<?php

declare(strict_types=1);

namespace atk4\login\demo;

use atk4\login\Model\Role;
use atk4\login\RoleAdmin;
use atk4\ui\Header;

include 'init.php';

Header::addTo($app)->set('Roles');

$crud = RoleAdmin::addTo($app);
$crud->setModel(new Role($app->db));

Header::addTo($app)->set('Roles new crud');

$new_crud = \atk4\login\RoleAdmin2::addTo($app);
$new_crud->setModel(new Role($app->db));

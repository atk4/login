<?php

declare(strict_types=1);

namespace Atk4\Login\Demo;

use Atk4\Login\Model\Role;
use Atk4\Login\RoleAdmin;
use Atk4\Ui\Header;

include 'init.php';

Header::addTo($app)->set('Roles');

$crud = RoleAdmin::addTo($app);
$crud->setModel(new Role($app->db));

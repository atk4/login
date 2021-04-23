<?php

declare(strict_types=1);

namespace Atk4\Login\Demo;

use Atk4\Login\Model\Role;
use Atk4\Login\RoleAdmin;
use Atk4\Ui\Header;

/** @var App $app */
include __DIR__ . '/init.php';

Header::addTo($app)->set('Roles');

RoleAdmin::addTo($app)->setModel(new Role($app->db));

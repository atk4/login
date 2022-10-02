<?php

declare(strict_types=1);

namespace Atk4\Login\Demos;

use Atk4\Login\Model\User;
use Atk4\Login\UserAdmin;
use Atk4\Ui\Header;

/** @var App $app */
require_once __DIR__ . '/init-app.php';

Header::addTo($app)->set('Users');
UserAdmin::addTo($app)->setModel(new User($app->db));

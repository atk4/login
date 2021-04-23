<?php

declare(strict_types=1);

namespace Atk4\Login\Demo;

use Atk4\Login\Model\User;
use Atk4\Login\UserAdmin;
use Atk4\Ui\Header;

/** @var App $app */
include __DIR__ . '/init.php';

Header::addTo($app)->set('Users');
$app->add(new UserAdmin())
    ->setModel(new User($app->db));

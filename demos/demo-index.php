<?php

declare(strict_types=1);

namespace atk4\login\demo;

use atk4\ui\Button;
use atk4\ui\Header;
use atk4\ui\View;

require 'demo-init.php';

Header::addTo($app, ['This is demo app']);

if (isset($app->auth) && $app->auth->isLoggedIn()) {
    View::addTo($app)->set('Currently logged in: ' . $app->auth->user->getTitle());
} else {
    View::addTo($app)->set('Currently there is no user logged in');
}

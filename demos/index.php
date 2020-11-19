<?php

declare(strict_types=1);

namespace atk4\login\demo;

use atk4\ui\Button;
use atk4\ui\Header;
use atk4\ui\Message;
use atk4\ui\View;

require 'init.php';

Header::addTo($app, ['Welcome to Auth Add-on demo app']);

// Setup db by using migration
$v = View::addTo($app, ['ui' => 'segment']);
Button::addTo($v, ['Setup demo SQLite database', 'icon' => 'cogs'])->link(['admin-setup']);

// Info
if (isset($app->auth) && $app->auth->isLoggedIn()) {
    $a = Message::addTo($app, ['type' => 'info'])->set('Currently logged in: ' . $app->auth->user->getTitle());
    Button::addTo($a, ['Logout', 'icon' => 'sign out'])->on('click', $app->jsRedirect([$app->auth->pageDashboard, 'logout' => true]));
} else {
    $a = Message::addTo($app, ['type' => 'info'])->set('Currently there is no user logged in');
    Button::addTo($a, ['Login', 'icon' => 'key'])->on('click', $app->jsRedirect(['form-login']));
}

// Addon description
$v = View::addTo($app, ['ui' => 'segment']);
$v->set('Here goes small description of this addon');

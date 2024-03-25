<?php

declare(strict_types=1);

namespace Atk4\Login\Demos;

use Atk4\Ui\Button;
use Atk4\Ui\Header;
use Atk4\Ui\Message;
use Atk4\Ui\Text;
use Atk4\Ui\View;

/** @var App $app */
require_once __DIR__ . '/init-app.php';

Header::addTo($app, ['Welcome to Auth Add-on demo app']);

// Setup db by using migration
$v = View::addTo($app, ['ui' => 'segment']);
Button::addTo($v, ['Setup demo SQLite database', 'icon' => 'cogs'])->link(['admin-setup']);

// Info
if ($app->auth->isLoggedIn()) {
    $a = Message::addTo($app, ['type' => 'info'])->set('Currently logged in: ' . $app->auth->user->getTitle());
    Button::addTo($a, ['Logout', 'icon' => 'sign out'])->link([$app->auth->pageDashboard, 'logout' => 1]);
} else {
    $a = Message::addTo($app, ['type' => 'info'])->set('Currently there is no user logged in');
    Button::addTo($a, ['Login', 'icon' => 'key'])->link(['form-login']);
}

// Addon description
Text::addTo(View::addTo($app, ['ui' => 'segment']))
    ->addParagraph('ATK UI implements a high-level User Interface for Web App - such as Admin System. One of the most common things for the Admin system is a log-in screen.')
    ->addParagraph('Although you can implement log-in form easily, this add-on does everything for you.');

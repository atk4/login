<?php

declare(strict_types=1);

namespace atk4\login\demo;

use atk4\ui\Button;
use atk4\ui\Header;
use atk4\ui\View;

require 'init.php';

Header::addTo($app, ['Welcome to Auth Add-on demo app']);

// Setup db by using migration
$v = View::addTo($app, ['ui' => 'segment']);
Button::addTo($v, ['Setup demo SQLite database', 'icon' => 'cogs'])->link(['admin-setup']);

// Addon description
$v = View::addTo($app, ['ui' => 'segment']);
$v->set('Here goes small description of this addon');

<?php

declare(strict_types=1);

namespace atk4\login\demo;

use atk4\ui\Button;
use atk4\ui\Header;
use atk4\ui\View;

require '../vendor/autoload.php';
require 'db.php';

$app = new App(\atk4\ui\Layout\Centered::class, false, true);
Header::addTo($app, ['Welcome to Auth Add-on demo app']);
Button::addTo($app, ['Run migration wizard', 'icon' => 'gift'])->link(['wizard']);

View::addTo($app, ['ui' => 'divider']);
Button::addTo($app, ['Log-in', 'icon' => 'sign in'])->link(['login']);
Button::addTo($app, ['Register', 'icon' => 'edit'])->link(['register']);
Button::addTo($app, ['Dashboard', 'icon' => 'dashboard'])->link(['dashboard']);

View::addTo($app, ['ui' => 'divider']);
Button::addTo($app, ['Admin', 'icon' => 'lock open'])->link(['admin-users']);

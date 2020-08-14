<?php

namespace atk4\login\demo;

require '../vendor/autoload.php';
require 'db.php';

$app = new App('centered', false, true);

\atk4\ui\Header::addTo($app, ['Welcome to Auth Add-on demo app']);
\atk4\ui\Button::addTo($app, ['Run migration wizard', 'icon'=>'gift'])->link(['wizard']);

\atk4\ui\View::addTo($app, ['ui'=>'divider']);

\atk4\ui\Button::addTo($app, ['Log-in', 'icon'=>'sign in'])->link(['login']);
\atk4\ui\Button::addTo($app, ['Register', 'icon'=>'edit'])->link(['register']);
\atk4\ui\Button::addTo($app, ['Dashboard', 'icon'=>'dashboard'])->link(['dashboard']);

\atk4\ui\View::addTo($app, ['ui'=>'divider']);
\atk4\ui\Button::addTo($app, ['Admin', 'icon'=>'lock open'])->link(['admin-users']);

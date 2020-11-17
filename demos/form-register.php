<?php

declare(strict_types=1);

namespace atk4\login\demo;

use atk4\login\Model\User;
use atk4\login\RegisterForm;
use atk4\ui\Header;
use atk4\ui\View;

require 'init.php';

Header::addTo($app, ['New user sign-up form']);

$v = View::addTo($app, ['ui' => 'segment']);
$f = RegisterForm::addTo($v);
$m = new User($app->db);
$f->setModel($m);

<?php

declare(strict_types=1);

namespace atk4\login\demo;

use atk4\login\Model\User;
use atk4\login\LoginForm;
use atk4\ui\Header;
use atk4\ui\View;

require 'init.php';

Header::addTo($app, ['Login form']);

$v = View::addTo($app, ['ui' => 'segment']);
$f = LoginForm::addTo($v, [
    'linkForgot' => ['form-forgot'],
    'linkSuccess' => ['index'],
]);

//$app->auth->displayLoginForm();
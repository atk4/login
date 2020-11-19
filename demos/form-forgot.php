<?php

declare(strict_types=1);

namespace atk4\login\demo;

use atk4\login\Model\User;
use atk4\ui\Header;
use atk4\ui\View;

require 'init.php';

Header::addTo($app, ['Forgot password form']);

$v = View::addTo($app, ['ui' => 'segment']);
$v->set('Not implemented');
/*
$f = Form\ForgotPassword::addTo($v, [
    'linkSuccess' => ['index'],
]);
*/

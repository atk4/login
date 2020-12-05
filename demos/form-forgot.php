<?php

declare(strict_types=1);

namespace Atk4\Login\Demo;

use Atk4\Ui\Header;
use Atk4\Ui\View;

require 'init.php';

Header::addTo($app, ['Forgot password form']);

$v = View::addTo($app, ['ui' => 'segment']);
$v->set('Not implemented');
/*
$f = Form\ForgotPassword::addTo($v, [
    'linkSuccess' => ['index'],
]);
*/

<?php

declare(strict_types=1);

namespace Atk4\Login\Demo;

use Atk4\Ui\Header;
use Atk4\Ui\View;

/** @var App $app */
require __DIR__ . '/init.php';

Header::addTo($app, ['Forgot password form']);
View::addTo($app, ['ui' => 'segment'])->set('Not implemented');
/*
$f = Form\ForgotPassword::addTo($v, [
    'linkSuccess' => ['index'],
]);
*/

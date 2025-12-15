<?php

declare(strict_types=1);

namespace Atk4\Login\Demos;

use Atk4\Ui\Header;
use Atk4\Ui\ViewWithContent;

/** @var App $app */
require_once __DIR__ . '/init-app.php';

Header::addTo($app, ['Forgot password form']);
ViewWithContent::addTo($app, ['ui' => 'segment'])->set('Not implemented');
/*
$f = Form\ForgotPassword::addTo($v, [
    'linkSuccess' => ['index'],
]);
*/

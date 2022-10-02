<?php

declare(strict_types=1);

namespace Atk4\Login\Demos;

/** @var App $app */
require_once __DIR__ . '/init-app.php';

$app->auth->logout();
$app->auth->displayLoginForm(['linkSuccess' => ['index']]);

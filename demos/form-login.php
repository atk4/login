<?php

declare(strict_types=1);

namespace Atk4\Login\Demo;

/** @var App $app */
require __DIR__ . '/init-app.php';

$app->auth->logout();
$app->auth->displayLoginForm();

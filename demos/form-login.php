<?php

declare(strict_types=1);

namespace Atk4\Login\Demo;

require 'init.php';

$app->auth->logout();
$app->auth->displayLoginForm();

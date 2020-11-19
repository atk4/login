<?php

declare(strict_types=1);

namespace atk4\login\demo;

require 'init.php';

$app->auth->logout();
$app->auth->displayLoginForm();

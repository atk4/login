<?php

declare(strict_types=1);

namespace atk4\login\demo;

use atk4\login\Form;
use atk4\ui\Header;
use atk4\ui\View;

require 'init.php';

$app->auth->logout();
$app->auth->displayLoginForm();

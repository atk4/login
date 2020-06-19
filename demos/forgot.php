<?php

declare(strict_types=1);

namespace atk4\login\demo;

require '../vendor/autoload.php';
require 'db.php';

$app = new App(false);

$app->add(new \atk4\login\Auth\Temporary('demo', 'demo'));

$app->add(['Text', 'You are authenticated']);

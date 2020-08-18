<?php

declare(strict_types=1);

namespace atk4\login\demo;

include '../vendor/autoload.php';
include 'db.php';

$app = new App(false);
$app->add([View::class, 'defaultTemplate' => dirname(__DIR__) . '/template/all.html'], 'Section');

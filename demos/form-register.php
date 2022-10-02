<?php

declare(strict_types=1);

namespace Atk4\Login\Demos;

use Atk4\Login\Form;
use Atk4\Login\Layout\Narrow;
use Atk4\Login\Model\User;
use Atk4\Ui\Header;

/** @var App $app */
require_once __DIR__ . '/init-app.php';

$app->html = null;
$app->initLayout([Narrow::class]);
Header::addTo($app, ['New user sign-up form']);

$f = Form\Register::addTo($app, ['auth' => $app->auth]);
$m = new User($app->db);
$f->setModel($m->createEntity());

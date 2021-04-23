<?php

declare(strict_types=1);

namespace Atk4\Login\Demo;

use Atk4\Login\Form;
use Atk4\Login\Model\User;
use Atk4\Ui\Header;
use Atk4\Ui\View;

/** @var App $app */
require __DIR__ . '/init.php';

Header::addTo($app, ['New user sign-up form']);

$v = View::addTo($app, ['ui' => 'segment']);
$f = Form\Register::addTo($v);
$m = new User($app->db);
$f->setModel($m);

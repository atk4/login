<?php

declare(strict_types=1);

namespace Atk4\Login\Demos;

use Atk4\Ui\Crud;
use Atk4\Ui\Header;
use Atk4\Ui\Message;

/** @var App $app */
require_once __DIR__ . '/init-app.php';

Header::addTo($app, [
    'Client list for ACL testing',
    'subHeader' => 'Logged in as ' . $app->auth->user->getTitle(),
]);

// switch on ACL so it will be applied for all models added to persistence from now on
$app->initAcl();

Message::addTo($app, ['type' => 'info'])
    ->set('This is how an ACL managed app will look like based on logged in user and his role and permissions.');

Crud::addTo($app)->setModel(new Model\Client($app->db));

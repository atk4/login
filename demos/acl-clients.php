<?php

declare(strict_types=1);

namespace atk4\login\demo;

use atk4\ui\Crud;
use atk4\ui\Header;

include 'init.php';

Header::addTo($app, [
    'Client list for ACL testing',
    'subHeader' => 'Logged in as ' . $app->auth->user->getTitle(),
]);

// switch on ACL so it will be applied for all models added to persistence from now on
$app->initAcl();

$app->add(new Crud())
    ->setModel(new Model\Client($app->db));

<?php

declare(strict_types=1);

namespace atk4\login\demo;

include '../vendor/autoload.php';
include 'db.php';

use atk4\ui\Button;
use atk4\ui\Form;
use atk4\ui\Message;
use atk4\ui\Modal;

$app = new App(false);

$t = $app->add([Message::class, 'Currently Logged User'])->text;

if ($app->auth->user->loaded()) {
    $t->addParagraph($app->auth->user->get('email') . ' (' . $app->auth->user->getId() . ')');

    $app->add([Button::class, 'Profile', 'primary'])->on('click', $app->add([Modal::class])->set(function ($p) {
        $p->add([Form::class])->setModel($p->app->auth->user);
    })->show());

    $app->add([Button::class, 'Logout'])->on('click', function () use ($app) {
        $app->auth->logout();

        return new \atk4\ui\jsExpression('document.location.reload()');
    });
} else {
    $t->addParagraph('no user logged');
}

$app->add([Button::class, 'Back'])->link(['index']);

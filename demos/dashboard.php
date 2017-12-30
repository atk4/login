<?php

include '../vendor/autoload.php';
include 'db.php';

$app = new App(false);

$t = $app->add(['Message', 'Currently Logged User'])->text;

if ($app->auth->user->loaded()) {
    $t->addParagraph($app->auth->user['email'].' ('.$app->auth->user->id.')');

    $app->add(['Button', 'Profile', 'primary'])->on('click', $app->add('Modal')->set(function($p) {
        $p->add('Form')->setModel($p->app->auth->user);
    })->show());

    $app->add(['Button', 'Logout'])->on('click', function() use($app) {
        $app->auth->logout();
        return new \atk4\ui\jsExpression('document.location.reload()');
    });
} else {
    $t->addParagraph('no user logged');
}


$app->add(['Button', 'Back'])->link(['index']);

<?php

include '../vendor/autoload.php';
include 'db.php';

$app = new \atk4\ui\App('Auth Demo App');
$app->initLayout('Centered');
 
$app->add(['Header', 'Quickly checking if database is OK']);
$console = $app->add('Console');

$button = $app->add(['Button', '<< Back', 'huge wide blue'])
    ->addStyle('display', 'none')
    ->link(['index']);

$console->set(function($c) use ($button) {
    $c->output('Connecting..');

    $config = include('config.php');

    $c->output(' .. using DSN '. $config['dsn']);
    $p = \atk4\data\Persistence::connect($config['dsn']);

    $c->output('Migrating Models');

    foreach(['User'] as $model) {
        $c->output(' .. '. $model);
        $model = $c->app->factory($model, null, '\atk4\login\Model');
        $p->add($model);

        $m = new \atk4\schema\Migration\MySQL($model);
        $m->migrate();
    }

    $c->output('Everything looks good!');

    $c->send($button->js()->show());
});


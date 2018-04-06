<?php

include '../vendor/autoload.php';
include 'db.php';

$app = new App('centered');

$app->add(['Header', 'Quickly checking if database is OK']);
$console = $app->add('\atk4\schema\MigratorConsole');

$button = $app->add(['Button', '<< Back', 'huge wide blue'])
    ->addStyle('display', 'none')
    ->link(['index']);

$console->migrateModels(['\atk4\login\Model\User']);

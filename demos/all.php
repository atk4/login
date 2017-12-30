<?php

include '../vendor/autoload.php';
include 'db.php';

$app = new App(false);
$app->add(['defaultTemplate'=>dirname(__DIR__).'/template/all.html'], 'Section');

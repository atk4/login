<?php

include '../vendor/autoload.php';
include 'db.php';

$app = new App(false);

// stuff above the form
$c = $app->add('Columns');
$c->addColumn(12)->add(['Header', 'Log into your account', 'size'=>2]);
$c->addColumn(4)->add(['Button', 'Back', 'icon'=>'home', 'right floated tiny basic green'])
    ->link(['index']);
$app->add(['ui'=>'hidden divider']);

// form itself
$app->add([new \atk4\login\LoginForm(), 'auth'=>$app->auth]);

// below the form - signup link
$seg = $app->add(['ui'=>'secondary segment', 'class'=>['center aligned padded']], 'Segment');
$seg->add(['Text', 'Don\'t have account? &nbsp;&nbsp;']);
$l = $seg->add([])->link(['register']);
$l->add(['Text', 'Sign up']);
$l->add(['Icon', 'angle right']);

<?php

namespace atk4\login\demo;

use atk4\login\LoginForm;
use atk4\ui\Columns;
use atk4\ui\Icon;
use atk4\ui\Text;
use atk4\ui\View;

include '../vendor/autoload.php';
include 'db.php';

$app = new App(false);

// stuff above the form
$c = Columns::addTo($app);
$c->addColumn(12)->add(['Header', 'Log into your account', 'size'=>2]);
$c->addColumn(4)->add(['Button', 'Back', 'icon'=>'home', 'right floated tiny basic green'])
    ->link(['index']);
$app->add(['ui'=>'hidden divider']);

// form itself
LoginForm::addTo($app, ['auth'=>$app->auth]);

// below the form - signup link
$seg = $app->add(['ui'=>'secondary segment', 'class'=>['center aligned padded']], 'Segment');
$seg->add(['Text', 'Don\'t have account? &nbsp;&nbsp;']);
//$seg->add(['Text', 'Don\'t have account? &nbsp;&nbsp;']);
$l = View::addTo($seg)->link(['register']);
Text::addTo($l, ['Sign up']);
Icon::addTo($l, ['angle right']);

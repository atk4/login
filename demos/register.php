<?php

namespace atk4\login\demo;

use atk4\login\Model\User;
use atk4\login\RegisterForm;
use atk4\ui\Columns;
use atk4\ui\Icon;
use atk4\ui\Text;
use atk4\ui\View;

include '../vendor/autoload.php';
include 'db.php';

// App without authentication to be able to freely create new user
$app = new App(false, false, true);

// stuff above the form
$c = Columns::addTo($app);
$c->addColumn(12)->add(['Header', 'Create New Account', 'size' => 2]);
$c->addColumn(4)->add([
    'Button',
    'Back',
    'icon' => 'home',
    'right floated tiny basic green',
])->link(['index']);

View::addTo($app, ['ui' => 'hidden divider']);

// form itself
RegisterForm::addTo($app)->setModel(new User($app->db));

// below the form - signup link
$seg = $app->add([
    'ui' => 'secondary segment',
    'class' => ['center aligned padded'],
], 'Segment');

//$seg->add(['Text', 'Don\'t have account? &nbsp;&nbsp;']);
$l = View::addTo($seg)->link(['login']);
Icon::addTo($l, ['angle left']);
Text::addTo($l, ['Back to Login']);

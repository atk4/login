<?php

namespace atk4\login\demo;

include '../vendor/autoload.php';
include 'db.php';

$app = new App('centered', false, true); // App without authentication to be able to freely create new user

// stuff above the form
$c = \atk4\ui\Columns::addTo($app);

\atk4\ui\Header::addTo($c->addColumn(12), ['Create New Account', 'size'=>2]);
\atk4\ui\Button::addTo($c->addColumn(4), ['Back to login', 'icon'=>'home', 'right floated tiny basic green'])->link(['index']);

\atk4\ui\View::addTo($app, ['ui'=>'hidden divider']);
$app->add(new \atk4\login\RegisterForm())
    ->setModel(new \atk4\login\Model\User($app->db));


// form itself

// below the form - signup link
\atk4\ui\View::addTo($app, ['ui'=>'secondary segment', 'class'=>['center aligned padded']]);
//$seg->add(['Text', 'Don\'t have account? &nbsp;&nbsp;']);
//$l = $seg->add([])->link(['login']);
//\atk4\ui\Icon::addTo($app, ['angle left']);
//\atk4\ui\Text::addTo($app, ['Back to Login']);

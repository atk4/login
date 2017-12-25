<?php

$app = new \atk4\ui\App('Auth Test');

$app->add(new \atk4\login\Auth\Temporary('demo', 'demo'));

$app->add(['Text', 'You are authenticated']);

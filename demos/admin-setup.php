<?php

declare(strict_types=1);

namespace atk4\login\demo;

use atk4\login\Model\AccessRule;
use atk4\login\Model\Role;
use atk4\login\Model\User;
use atk4\ui\Button;
use atk4\ui\Console;
use atk4\ui\Header;
use atk4\ui\Message;
use atk4\ui\View;

require 'init.php';

Header::addTo($app, ['Setup demo database']);

$v = View::addTo($app, ['ui' => 'segment']);
Message::addTo($v, ['type' => 'warning'])->set('Be aware that running this migration will also reset all demo data you may have');

// setup migrator console
$c1 = MigratorConsole::addTo($v, ['event' => false]);

// after migration import data
$c1->onHook(MigratorConsole::HOOK_AFTER_MIGRATION, function($c){
    $c->notice('Populating data...');

    $rule = new AccessRule($c->getApp()->db);
    $rule->each(function ($m) {$m->delete(); });

    $role = new Role($c->getApp()->db);
    $role->each(function ($m) {$m->delete(); })
        ->import([
            ['name' => 'User Role'],
            ['name' => 'Admin Role'],
        ]);
    $c->debug('  Import roles.. OK');

    $user = new User($c->getApp()->db);
    $user->each(function ($m) {$m->delete(); })
        ->import([
            [
                'name' => 'Standard User',
                'email' => 'user',
                'role' => 'User Role',
                'password' => 'user',
            ],
            [
                'name' => 'Administrator',
                'email' => 'admin',
                'role' => 'Admin Role',
                'password' => 'admin',
            ],
        ]);
    $c->debug('  Import users.. OK');

    $rule->import([
        [
            'role' => 'Admin Role',
            'model' => '\\atk4\login\\Model\\User',
            'all_visible' => true,
            'all_editable' => true,
        ],
        [
            'role' => 'User Role',
            'model' => '\\atk4\login\\Model\\Role',
            'all_visible' => true,
            'all_editable' => false,
        ],
    ]);
    $c->debug('  Import roles.. OK');

    $c->notice('User created!');
    $c->debug('Username : user');
    $c->debug('Password : user');

    $c->notice('User created!');
    $c->debug('Username : admin');
    $c->debug('Password : admin');

    $c->notice('Data imported');
});

$c1->migrateModels([Role::class, User::class, AccessRule::class]);

// button to execute migration
$b = Button::addTo($v, ['Run migration', 'icon' => 'check']);
$b->on('click', function() use ($c1, $b){
    return [
        $c1->jsExecute(),
        $b->js()->hide(),
    ];
});

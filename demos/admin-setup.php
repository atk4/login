<?php

declare(strict_types=1);

namespace Atk4\Login\Demo;

use Atk4\Login\Demo\Model\Client;
use Atk4\Login\Model\AccessRule;
use Atk4\Login\Model\Role;
use Atk4\Login\Model\User;
use Atk4\Ui\Button;
use Atk4\Ui\Console;
use Atk4\Ui\Header;
use Atk4\Ui\Message;
use Atk4\Ui\View;

require 'init.php';

Header::addTo($app, ['Setup demo database']);

$v = View::addTo($app, ['ui' => 'segment']);
Message::addTo($v, ['type' => 'warning'])->set('Be aware that running this migration will also reset all demo data you may have');

// setup migrator console
$c1 = MigratorConsole::addTo($v, ['event' => false]);

// after migration import data
$c1->onHook(MigratorConsole::HOOK_AFTER_MIGRATION, function ($c) {
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
    $c->notice('User: admin/admin created.');
    $c->notice('User: user/user created.');
    $c->debug('  Import users.. OK');

    $rule->import([
        [
            'role' => 'Admin Role',
            'model' => Client::class,
            'all_visible' => true,
            'all_editable' => true,
            'all_actions' => true,
        ],
        [
            'role' => 'User Role',
            'model' => Client::class,
            'all_visible' => true,
            'all_editable' => false,
            'editable_fields' => 'vat_number,active',
            'all_actions' => false,
            'actions' => 'edit,test',
        ],
    ]);
    $c->debug('  Import roles.. OK');

    $client = new Client($c->getApp()->db);
    $client->each(function ($m) {$m->delete(); })
        ->import([
            ['name' => 'John Doe', 'vat_number' => 'GB1234567890', 'balance' => 1234.56, 'active' => true],
            ['name' => 'Jane Doe', 'vat_number' => null, 'balance' => 50, 'active' => true],
            ['name' => 'Pokemon', 'vat_number' => 'LV-13141516', 'balance' => 100.65, 'active' => true],
            ['name' => 'Captain Jack', 'vat_number' => null, 'balance' => -600, 'active' => false],
        ]);
    $c->debug('  Import clients.. OK');

    $c->notice('Data imported');
});

$c1->migrateModels([Role::class, User::class, AccessRule::class, Client::class]);

// button to execute migration
$b = Button::addTo($v, ['Run migration', 'icon' => 'check']);
$b->on('click', function () use ($c1, $b) {
    return [
        $c1->jsExecute(),
        $b->js()->hide(),
    ];
});

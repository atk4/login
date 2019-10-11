<?php
namespace atk4\login\demo;

use atk4\login\Model\AccessRule;
use atk4\login\Model\Role;
use atk4\login\Model\User;
use atk4\schema\MigratorConsole;
use atk4\ui\Console;
use atk4\ui\View;
use atk4\ui\Wizard;

include '../vendor/autoload.php';
include 'db.php';

$app = new App('centered', false, true); // App without authentication to be able to freely import data

/** @var Wizard $wizard */
$wizard=$app->add('Wizard');

$wizard->addStep('Quickly checking if database is OK', function(View $page) {
    $console = $page->add(MigratorConsole::class);

    /*
    $button = $page->add(['Button', '<< Back', 'huge wide blue'])
        ->addStyle('display', 'none')
        ->link(['index']);
    */

    $console->migrateModels([User::class, Role::class, AccessRule::class]);
});

$wizard->addStep('Populate Sample Data', function(View $page) {
    $page->add('Console')->set(function(Console $c) {

        $c->debug('Populating data...');

        (new AccessRule($c->app->db))
            ->each('delete');
        (new Role($c->app->db))
            ->each('delete')
            ->import(['User Role', 'Admin Role']);
        (new User($c->app->db))
            ->each('delete')
            ->import([
                ['name'=>'Standard User', 'email'=>'user', 'role'=>'User Role', 'password'=>'user'],
                ['name'=>'Administrator', 'email'=>'admin', 'role'=>'Admin Role', 'password'=>'admin'],
            ]);
        (new AccessRule($c->app->db))
            ->import([
                ['role'=>'Admin Role', 'model'=>'foo', 'all_visible'=>true, 'all_editable'=>true],
                ['role'=>'User Role', 'model'=>'bar', 'all_visible'=>true, 'all_editable'=>false, /*'editable_fields'=>['a','b']*/],
            ]);

        $c->debug('Data imported');
    });
});

$wizard->addFinish(function($p) {
    $p->app->redirect(['index']);
});

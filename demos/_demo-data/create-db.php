<?php

declare(strict_types=1);

namespace Atk4\Login\Demos;

use Atk4\Data\Model;
use Atk4\Data\Persistence;
use Atk4\Data\Schema\Migrator;
use Atk4\Login\Model\Role;
use Atk4\Login\Model\User;

require_once __DIR__ . '/../init-autoloader.php';

$sqliteFile = __DIR__ . '/db.sqlite';
if (!file_exists($sqliteFile)) {
    new Persistence\Sql('sqlite:' . $sqliteFile);
}
unset($sqliteFile);

/** @var Persistence\Sql $db */
require_once __DIR__ . '/../init-db.php';

$model = new Model($db, ['table' => 'login_role']);
$model->addField('name', ['type' => 'string']);
(new Migrator($model))->create();
$model->import([
    ['id' => 1, 'name' => 'User Role'],
    ['id' => 2, 'name' => 'Admin Role'],
]);

$model = new Model($db, ['table' => 'login_user']);
$model->addField('name', ['type' => 'string']);
$model->addField('email', ['type' => 'string']);
$model->addField('password', ['type' => 'string']);
$model->addField('role_id', ['type' => 'integer']);
(new Migrator($model))->create();
$model->import([
    ['id' => 1, 'name' => 'Standard User', 'email' => 'user', 'password' => '$2y$10$BwEhcP8f15yOexf077VTHOnySn/mit49ZhpfeBkORQhrsmHr4U6Qy' /* user */ , 'role_id' => 1],
    ['id' => 2, 'name' => 'Administrator', 'email' => 'admin', 'password' => '$2y$10$p34ciRcg9GZyxukkLIaEnenGBao79fTFa4tFSrl7FvqrxnmEGlD4O' /* admin */, 'role_id' => 2],
]);

$model = new Model($db, ['table' => 'login_access_rule']);
$model->addField('role_id', ['type' => 'integer']);
$model->addField('model', ['type' => 'string']);
$model->addField('all_visible', ['type' => 'boolean']);
$model->addField('visible_fields', ['type' => 'boolean']);
$model->addField('all_editable', ['type' => 'boolean']);
$model->addField('editable_fields', ['type' => 'boolean']);
$model->addField('all_actions', ['type' => 'boolean']);
$model->addField('actions', ['type' => 'boolean']);
$model->addField('conditions', ['type' => 'boolean']);

(new Migrator($model))->create();
$model->import([
    ['id' => 1, 'role_id' => 1, 'model' => User::class, 'all_visible' => 1, 'visible_fields' => null, 'all_editable' => 0, 'editable_fields' => null, 'all_actions' => 1, 'actions' => null, 'conditions' => null],
    ['id' => 2, 'role_id' => 2, 'model' => User::class, 'all_visible' => 1, 'visible_fields' => null, 'all_editable' => 1, 'editable_fields' => null, 'all_actions' => 1, 'actions' => null, 'conditions' => null],
    ['id' => 3, 'role_id' => 2, 'model' => Role::class, 'all_visible' => 1, 'visible_fields' => null, 'all_editable' => 1, 'editable_fields' => null, 'all_actions' => 1, 'actions' => null, 'conditions' => null],
]);

$model = new Model($db, ['table' => 'demo_client']);
$model->addField('name', ['required' => true]);
$model->addField('vat_number');
$model->addField('balance', ['type' => 'atk4_money']);
$model->addField('active', ['type' => 'boolean', 'default' => true]);

(new Migrator($model))->create();

echo 'import complete!' . "\n\n";

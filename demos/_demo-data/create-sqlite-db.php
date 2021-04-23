<?php

declare(strict_types=1);

namespace Atk4\Login\Demos;

use Atk4\Data\Model;

include __DIR__ . '/../../vendor/autoload.php';

$sqliteFile = __DIR__ . '/../data/db.sqlite';
if (file_exists($sqliteFile)) {
    unlink($sqliteFile);
}

$persistence = new \Atk4\Data\Persistence\Sql('sqlite:' . $sqliteFile);
$model = new \Atk4\Data\Model($persistence, ['table' => 'login_user']);
$model->addField('name', ['type' => 'string']);
$model->addField('email', ['type' => 'string']);
$model->addField('password', ['type' => 'string']);
$model->addField('role_id', ['type' => 'integer']);
(new \Atk4\Schema\Migration($model))->dropIfExists()->create();
$model->import([
    1 => ['id' => 1, 'name' => 'Standard User', 'email' => 'user', 'password' => '$2y$10$BwEhcP8f15yOexf077VTHOnySn/mit49ZhpfeBkORQhrsmHr4U6Qy', 'role_id' => 1], // user/user
    2 => ['id' => 2, 'name' => 'Administrator', 'email' => 'admin', 'password' => '$2y$10$p34ciRcg9GZyxukkLIaEnenGBao79fTFa4tFSrl7FvqrxnmEGlD4O', 'role_id' => 2], // admin/admin
]);

$model = new \Atk4\Data\Model($persistence, ['table' => 'login_role']);
$model->addField('name', ['type' => 'string']);
(new \Atk4\Schema\Migration($model))->dropIfExists()->create();
$model->import([
    1 => ['id' => 1, 'name' => 'User Role'],
    2 => ['id' => 2, 'name' => 'Admin Role'],
]);

$model = new \Atk4\Data\Model($persistence, ['table' => 'login_access_role']);
$model->addField('role_id', ['type' => 'integer']);
$model->addField('model', ['type' => 'string']);
$model->addField('all_visible', ['type' => 'boolean']);
$model->addField('visible_fields', ['type' => 'boolean']);
$model->addField('all_editable', ['type' => 'boolean']);
$model->addField('editable_fields', ['type' => 'boolean']);
$model->addField('all_actions', ['type' => 'boolean']);
$model->addField('actions', ['type' => 'boolean']);
$model->addField('conditions', ['type' => 'boolean']);

(new \Atk4\Schema\Migration($model))->dropIfExists()->create();
$model->import([
    1 => ['id' => 1, 'role_id' => 1, 'model' => '\\Atk4\Login\\Model\\User', 'all_visible' => 1, 'visible_fields' => null, 'all_editable' => 0, 'editable_fields' => null, 'all_actions' => 1, 'actions' => null, 'conditions' => null],
    2 => ['id' => 2, 'role_id' => 2, 'model' => '\\Atk4\Login\\Model\\User', 'all_visible' => 1, 'visible_fields' => null, 'all_editable' => 1, 'editable_fields' => null, 'all_actions' => 1, 'actions' => null, 'conditions' => null],
    3 => ['id' => 3, 'role_id' => 2, 'model' => '\\Atk4\Login\\Model\\Role', 'all_visible' => 1, 'visible_fields' => null, 'all_editable' => 1, 'editable_fields' => null, 'all_actions' => 1, 'actions' => null, 'conditions' => null],
]);

echo 'import complete!' . "\n";

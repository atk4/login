<?php

declare(strict_types=1);

namespace Atk4\Login\Tests;

use Atk4\Data\Model;
use Atk4\Data\Schema\TestCase as BaseTestCase;
use Atk4\Login\Model\AccessRule;
use Atk4\Login\Model\Role;
use Atk4\Login\Model\User;

abstract class GenericTestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function setupDefaultDb(): void
    {
        $this->setDb([
            self::getTableByStandardModelClass(User::class) => [
                1 => ['id' => 1, 'name' => 'Standard User', 'email' => 'user', 'password' => '$2y$10$BwEhcP8f15yOexf077VTHOnySn/mit49ZhpfeBkORQhrsmHr4U6Qy', 'role_id' => 1], // user/user
                2 => ['id' => 2, 'name' => 'Administrator', 'email' => 'admin', 'password' => '$2y$10$p34ciRcg9GZyxukkLIaEnenGBao79fTFa4tFSrl7FvqrxnmEGlD4O', 'role_id' => 2], // admin/admin
            ],
            self::getTableByStandardModelClass(Role::class) => [
                1 => ['id' => 1, 'name' => 'User Role'],
                2 => ['id' => 2, 'name' => 'Admin Role'],
            ],
            self::getTableByStandardModelClass(AccessRule::class) => [
                1 => ['id' => 1, 'role_id' => 1, 'model' => User::class, 'all_visible' => 1, 'visible_fields' => null, 'all_editable' => 0, 'editable_fields' => null, 'all_actions' => 1, 'actions' => null, 'conditions' => null],
                2 => ['id' => 2, 'role_id' => 2, 'model' => User::class, 'all_visible' => 1, 'visible_fields' => null, 'all_editable' => 1, 'editable_fields' => null, 'all_actions' => 1, 'actions' => null, 'conditions' => null],
                3 => ['id' => 3, 'role_id' => 2, 'model' => Role::class, 'all_visible' => 1, 'visible_fields' => null, 'all_editable' => 1, 'editable_fields' => null, 'all_actions' => 1, 'actions' => null, 'conditions' => null],
            ],
        ]);
    }

    private static function getTableByStandardModelClass(string $modelClass): string
    {
        return [
            User::class => 'unit_user',
            Role::class => 'unit_role',
            AccessRule::class => 'unit_access_rule',
        ][$modelClass];
    }

    public static function replaceTableAndModelsInRefs(Model $model): void
    {
        $model->table = self::getTableByStandardModelClass(get_parent_class($model)); // @phpstan-ignore-line https://github.com/phpstan/phpstan/issues/4302
        foreach ($model->getRefs() as $k => $r) {
            if ($r->model instanceof \Closure) {
                if ($model instanceof User && $k === 'AccessRules') { // safe Closure, reference is build using ->ref()
                    continue;
                }
            }

            $r->model[0] = self::getClassByStandardModelClass($r->model[0]);
        }
    }

    private static function getClassByStandardModelClass(string $modelClass): string
    {
        return get_class([
            User::class => function () {
                return new class() extends User {
                    public $table = '';

                    protected function init(): void
                    {
                        parent::init();

                        GenericTestCase::replaceTableAndModelsInRefs($this);
                    }
                };
            },
            Role::class => function () {
                return new class() extends Role {
                    public $table = '';

                    protected function init(): void
                    {
                        parent::init();

                        GenericTestCase::replaceTableAndModelsInRefs($this);
                    }
                };
            },
            AccessRule::class => function () {
                return new class() extends AccessRule {
                    public $table = '';

                    protected function init(): void
                    {
                        parent::init();

                        GenericTestCase::replaceTableAndModelsInRefs($this);
                    }
                };
            },
        ][$modelClass]());
    }

    private function createModelByStandardModelClass(string $modelClass): Model
    {
        $class = self::getClassByStandardModelClass($modelClass);

        return new $class($this->db);
    }

    protected function createUserModel(): User
    {
        return $this->createModelByStandardModelClass(User::class); // @phpstan-ignore-line
    }

    protected function createRoleModel(): Role
    {
        return $this->createModelByStandardModelClass(Role::class); // @phpstan-ignore-line
    }

    protected function createAccessRuleModel(): AccessRule
    {
        return $this->createModelByStandardModelClass(AccessRule::class); // @phpstan-ignore-line
    }
}

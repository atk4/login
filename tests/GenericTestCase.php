<?php

declare(strict_types=1);

namespace Atk4\Login\Tests;

use Atk4\Data\Model;
use Atk4\Data\Schema\TestCase as BaseTestCase;
use Atk4\Login\Model\AccessRule;
use Atk4\Login\Model\Role;
use Atk4\Login\Model\User;
use Atk4\Ui\App;
use Atk4\Ui\Tests\CreateAppTrait;

abstract class GenericTestCase extends BaseTestCase
{
    use CreateAppTrait;

    /**
     * Put model table names here. Needed for DB import.
     */
    private static array $modelTables = [
        Role::class => 'login_role',
        User::class => 'login_user',
        AccessRule::class => 'login_access_rule',
    ];

    #[\Override]
    protected function tearDown(): void
    {
        \Closure::bind(static function () {
            App\SessionManager::$readCache = null;
        }, null, App\SessionManager::class)();

        parent::tearDown();
    }

    protected function createAppForSession(): App
    {
        $app = $this->createApp([
            'catchExceptions' => false,
            'alwaysRun' => false,
        ]);

        $app->session = new class() extends App\SessionManager {
            /** @var array<string, mixed> */
            private $data = [];

            /** @var bool */
            private $isActive = false;

            protected function isSessionActive(): bool
            {
                return $this->isActive;
            }

            protected function startSession(bool $readAndCloseImmediately): void
            {
                $_SESSION = $this->data;

                if (!$readAndCloseImmediately) {
                    $this->isActive = true;
                }
            }

            protected function closeSession(bool $writeBeforeClose): void
            {
                if ($writeBeforeClose) {
                    $this->data = $_SESSION;
                }

                $this->isActive = false;
            }
        };

        return $app;
    }

    protected function setupDefaultDb(): void
    {
        $this->setDb([
            self::getTableByModelClass(Role::class) => [
                ['id' => 1, 'name' => 'User Role'],
                ['id' => 2, 'name' => 'Admin Role'],
            ],
            self::getTableByModelClass(User::class) => [
                ['id' => 1, 'name' => 'Standard User', 'email' => 'user', 'password' => '$2y$10$BwEhcP8f15yOexf077VTHOnySn/mit49ZhpfeBkORQhrsmHr4U6Qy' /* user */, 'role_id' => 1],
                ['id' => 2, 'name' => 'Administrator', 'email' => 'admin', 'password' => '$2y$10$p34ciRcg9GZyxukkLIaEnenGBao79fTFa4tFSrl7FvqrxnmEGlD4O' /* admin */, 'role_id' => 2],
            ],
            self::getTableByModelClass(AccessRule::class) => [
                ['id' => 1, 'role_id' => 1, 'model' => User::class, 'all_visible' => true, 'visible_fields' => null, 'all_editable' => false, 'editable_fields' => 'vat_number,active', 'all_actions' => true, 'actions' => null, 'conditions' => null],
                ['id' => 2, 'role_id' => 2, 'model' => User::class, 'all_visible' => true, 'visible_fields' => null, 'all_editable' => false, 'editable_fields' => null, 'all_actions' => true, 'actions' => null, 'conditions' => null],
                ['id' => 3, 'role_id' => 2, 'model' => Role::class, 'all_visible' => true, 'visible_fields' => null, 'all_editable' => true, 'editable_fields' => null, 'all_actions' => true, 'actions' => null, 'conditions' => null],
            ],
        ]);
    }

    private static function getTableByModelClass(string $modelClass): string
    {
        return self::$modelTables[$modelClass];
    }

    private function createModel(string $modelClass): Model
    {
        return new $modelClass($this->db);
    }

    protected function createRoleModel(): Role
    {
        return $this->createModel(Role::class); // @phpstan-ignore-line
    }

    protected function createUserModel(): User
    {
        return $this->createModel(User::class); // @phpstan-ignore-line
    }

    protected function createAccessRuleModel(): AccessRule
    {
        return $this->createModel(AccessRule::class); // @phpstan-ignore-line
    }
}

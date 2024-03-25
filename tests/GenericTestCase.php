<?php

declare(strict_types=1);

namespace Atk4\Login\Tests;

use Atk4\Data\Schema\TestCase as BaseTestCase;
use Atk4\Login\Tests\Model\TestAccessRule as AccessRule;
use Atk4\Login\Tests\Model\TestRole as Role;
use Atk4\Login\Tests\Model\TestUser as User;
use Atk4\Ui\App;
use Atk4\Ui\Tests\CreateAppTrait;

abstract class GenericTestCase extends BaseTestCase
{
    use CreateAppTrait;

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
            self::getTableByStandardModelClass(Role::class) => [
                ['id' => 1, 'name' => 'User Role'],
                ['id' => 2, 'name' => 'Admin Role'],
            ],
            self::getTableByStandardModelClass(User::class) => [
                ['id' => 1, 'name' => 'Standard User', 'email' => 'user', 'password' => '$2y$10$BwEhcP8f15yOexf077VTHOnySn/mit49ZhpfeBkORQhrsmHr4U6Qy' /* user */, 'role_id' => 1, 'last_login' => null],
                ['id' => 2, 'name' => 'Administrator', 'email' => 'admin', 'password' => '$2y$10$p34ciRcg9GZyxukkLIaEnenGBao79fTFa4tFSrl7FvqrxnmEGlD4O' /* admin */, 'role_id' => 2, 'last_login' => null],
            ],
            self::getTableByStandardModelClass(AccessRule::class) => [
                ['id' => 1, 'role_id' => 1, 'model' => User::class, 'all_visible' => true, 'visible_fields' => null, 'all_editable' => false, 'editable_fields' => 'vat_number,active', 'all_actions' => true, 'actions' => null, 'conditions' => null],
                ['id' => 2, 'role_id' => 2, 'model' => User::class, 'all_visible' => true, 'visible_fields' => null, 'all_editable' => false, 'editable_fields' => null, 'all_actions' => true, 'actions' => null, 'conditions' => null],
                ['id' => 3, 'role_id' => 2, 'model' => Role::class, 'all_visible' => true, 'visible_fields' => null, 'all_editable' => true, 'editable_fields' => null, 'all_actions' => true, 'actions' => null, 'conditions' => null],
            ],
        ]);
    }

    private static function getTableByStandardModelClass(string $modelClass): string
    {
        return [
            Role::class => 'unit_role',
            User::class => 'unit_user',
            AccessRule::class => 'unit_access_rule',
        ][$modelClass];
    }

    protected function createRoleModel(): Role
    {
        return new Role($this->db);
    }

    protected function createUserModel(): User
    {
        return new User($this->db);
    }

    protected function createAccessRuleModel(): AccessRule
    {
        return new AccessRule($this->db);
    }
}

<?php

declare(strict_types=1);

namespace Atk4\Login\Demos;

use Atk4\Core\AppScopeTrait;
use Atk4\Core\DynamicMethodTrait;
use Atk4\Core\Factory;
use Atk4\Core\HookTrait;
use Atk4\Data\Model;
use Atk4\Data\Schema\Migrator;
use Atk4\Ui\Console;

class MigratorConsole extends Console
{
    use AppScopeTrait;
    use DynamicMethodTrait;
    use HookTrait;

    public const HOOK_BEFORE_MIGRATION = self::class . '@beforeMigration';
    public const HOOK_AFTER_MIGRATION = self::class . '@afterMigration';

    /** @var class-string */
    public string $migratorClass = Migrator::class;

    /**
     * Provided with array of models, perform migration for each of them.
     *
     * @param array<Model|array> $models
     */
    public function migrateModels(array $models): void
    {
        // run inside callback
        $this->set(function (self $console) use ($models) {
            $this->hook(self::HOOK_BEFORE_MIGRATION);

            $console->notice('Preparing to migrate models');

            foreach ($models as $model) {
                if (!is_object($model)) {
                    $model = Factory::factory($model);
                    $model->setPersistence($console->getApp()->db);
                }

                (new $this->migratorClass($model))->dropIfExists()->create(); // recreate table

                $console->debug('  ' . get_class($model) . '.. OK');
            }

            $console->notice('Done with migration');

            $this->hook(self::HOOK_AFTER_MIGRATION);
        });
    }
}

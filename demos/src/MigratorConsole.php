<?php

declare(strict_types=1);

namespace Atk4\Login\Demo;

use Atk4\Core\AppScopeTrait;
use Atk4\Core\DynamicMethodTrait;
use Atk4\Core\Factory;
use Atk4\Core\HookTrait;
use Atk4\Schema\Migration;
use Atk4\Ui\Console;

/**
 * Makes sure your database is adjusted for one or several models,
 * that you specify.
 */
class MigratorConsole extends Console
{
    use AppScopeTrait;
    use DynamicMethodTrait;
    use HookTrait;

    /** @const string */
    public const HOOK_BEFORE_MIGRATION = self::class . '@beforeMigration';

    /** @const string */
    public const HOOK_AFTER_MIGRATION = self::class . '@afterMigration';

    /** @var string Name of migrator class to use */
    public $migrator_class = Migration::class;

    /**
     * Provided with array of models, perform migration for each of them.
     *
     * @param array $models
     */
    public function migrateModels($models): void
    {
        // run inside callback
        $this->set(function ($console) use ($models) {
            $this->hook(self::HOOK_BEFORE_MIGRATION);

            $console->notice('Preparing to migrate models');

            foreach ($models as $model) {
                if (!is_object($model)) {
                    $model = Factory::factory((array) $model);
                    $console->getApp()->db->add($model);
                }

                (new $this->migrator_class($model))->dropIfExists()->create(); // recreate table

                $console->debug('  ' . get_class($model) . '.. OK');
            }

            $console->notice('Done with migration');

            $this->hook(self::HOOK_AFTER_MIGRATION);
        });
    }
}

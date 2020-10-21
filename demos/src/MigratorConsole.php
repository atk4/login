<?php

declare(strict_types=1);

use atk4\schema\Migration;
use atk4\ui\Console;

/**
 * Makes sure your database is adjusted for one or several models,
 * that you specify.
 */
class MigratorConsole extends Console
{
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
            $console->notice('Preparing to migrate models');

            foreach ($models as $model) {
                if (!is_object($model)) {
                    $model = $this->factory((array) $model);
                    $console->getApp()->db->add($model);
                }

                (new $this->migrator_class($model))->dropIfExists()->create(); // recreate table

                $console->debug('  ' . get_class($model) . '.. OK');
            }

            $console->notice('Done with migration');
        });
    }
}

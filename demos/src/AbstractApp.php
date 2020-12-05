<?php

declare(strict_types=1);

namespace Atk4\Login\Demo;

/**
 * Application which use demo database.
 */
abstract class AbstractApp extends \Atk4\Ui\App
{
    /** @var string DB filename */
    public $dbFile = '/data/db.sqlite';

    /** @var Persistence/Sql */
    public $db;

    protected function init(): void
    {
        parent::init();

        // set DB connection
        $this->dbFile = __DIR__ . '/..' . $this->dbFile;
        $this->db = new \Atk4\Data\Persistence\Sql('sqlite:' . $this->dbFile);
    }
}

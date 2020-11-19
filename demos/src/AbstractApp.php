<?php

declare(strict_types=1);

namespace atk4\login\demo;

/**
 * Application which use demo database.
 */
abstract class AbstractApp extends \atk4\ui\App
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
        $this->db = new \atk4\data\Persistence\Sql('sqlite:' . $this->dbFile);
    }
}

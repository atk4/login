<?php

declare(strict_types=1);

namespace atk4\login\demo;

use atk4\login\Acl;
use atk4\login\Auth;
use atk4\ui\Layout;

/**
 * Example implementation of your Authenticated application.
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
        //$app->db->setApp($app);
    }
}

<?php

declare(strict_types=1);

namespace atk4\login\demo;

use atk4\login\Acl;
use atk4\login\Auth;
use atk4\ui\Layout;

/**
 * Example implementation of your Authenticated application.
 */
class DemoApp extends AbstractApp
{
    public $auth;
    public $title = 'Auth Demo App in frame';

    protected function init(): void
    {
        parent::init();

        $this->initLayout([Layout\Centered::class]);

        $this->initAuth(true);
    }

    protected function initAuth($check = true)
    {
        $this->auth = new Auth(['check' => $check, 'pageDashboard' => 'demo-index']);
        $this->auth->setApp($this);

        // Can not setmodel at this stage :(
        $m = new \atk4\login\Model\User($this->db);
        $this->auth->setModel($m);

        // adding this requires user to be logged in, so we can't run this in wrapping app :(
        $this->auth->setAcl(new Acl(), $this->db);
    }
}

<?php

declare(strict_types=1);

namespace atk4\login\demo;

use atk4\login\Acl;
use atk4\login\Auth;
use atk4\ui\Layout;

/**
 * Example implementation of your Authenticated application.
 */
class App extends AbstractApp
{
    public $auth;
    public $title = 'Demo App';

    protected function init(): void
    {
        parent::init();

        $this->initLayout([Layout\Admin::class]);

        // Construct menu
        $this->layout->menuLeft->addItem(['Dashboard', 'icon' => 'info'], ['index']);
        $this->layout->menuLeft->addItem(['Setup demo database', 'icon' => 'cogs'], ['admin-setup']);

        $g = $this->layout->menuLeft->addGroup(['Forms']);
        $g->addItem(['Sign-up form', 'icon' => 'edit'], ['form-register']);
        $g->addItem(['Login form', 'icon' => 'edit'], ['form-login']);
        $g->addItem(['Forgot password form', 'icon' => 'edit'], ['form-forgot']);

        $g = $this->layout->menuLeft->addGroup(['Admin']);
        $g->addItem(['User Admin', 'icon' => 'users'], ['admin-users']);
        $g->addItem(['Role Admin', 'icon' => 'tasks'], ['admin-roles']);

        $g = $this->layout->menuLeft->addGroup(['App demo with ACL']);
        $g->addItem(['Client list (for ACL testing)', 'icon' => 'table'], ['acl-clients']);

        $this->initAuth(false);

        if ($this->auth->isLoggedIn()) {
            $this->auth->addUserMenu();
        }
    }

    public function initAuth($check = true)
    {
        $this->auth = new Auth(['check' => $check, 'pageDashboard' => 'index']);
        $this->auth->setApp($this);

        // Can not setmodel at this stage :(
        $m = new \atk4\login\Model\User($this->db);
        $this->auth->setModel($m);
    }

    public function initAcl()
    {
        // adding this requires user to be logged in, so we can't run this in wrapping app :(
        $this->auth->setAcl(new Acl(), $this->db);
    }
}

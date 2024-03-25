<?php

declare(strict_types=1);

namespace Atk4\Login\Demos;

use Atk4\Login\Acl;
use Atk4\Login\Auth;
use Atk4\Login\Model\User;
use Atk4\Ui\Layout;

class App extends \Atk4\Ui\App
{
    /** @var Auth */
    public $auth;

    public $title = 'Demo App';

    /** @var Layout\Admin */
    public $layout; // @phpstan-ignore-line

    public function init(): void
    {
        $this->initLayout([Layout\Admin::class]);

        // construct menu
        $this->layout->menuLeft->addItem(['Dashboard', 'icon' => 'info'], ['index']);
        $this->layout->menuLeft->addItem(['Setup demo database', 'icon' => 'cogs'], ['admin-setup']);

        $g = $this->layout->menuLeft->addGroup(['Forms']);
        $g->addItem(['Sign-up form', 'icon' => 'edit'], ['form-register']);
        $g->addItem(['Login form', 'icon' => 'edit'], ['form-login']);
        $g->addItem(['Forgot password form', 'icon' => 'edit'], ['form-forgot']);

        $g = $this->layout->menuLeft->addGroup(['Admin']);
        $g->addItem(['Users', 'icon' => 'users'], ['admin-users']);
        $g->addItem(['Roles', 'icon' => 'tasks'], ['admin-roles']);

        $g = $this->layout->menuLeft->addGroup(['App demo with ACL']);
        $g->addItem(['Client list (for ACL testing)', 'icon' => 'table'], ['acl-clients']);

        $this->initAuth(false);

        if ($this->auth->isLoggedIn()) {
            $this->auth->addUserMenu();
        }
    }

    public function initAuth(bool $check = true): void
    {
        $this->auth = new Auth($this, ['check' => $check, 'pageDashboard' => 'index']);

        // cannot set model at this stage :(
        $m = new User($this->db);
        $this->auth->setModel($m);
    }

    public function initAcl(): void
    {
        // adding this requires user to be logged in, so we can't run this in wrapping app :(
        $this->auth->setAcl(new Acl(), $this->db);
    }
}

<?php

declare(strict_types=1);

namespace atk4\login\demo;

use atk4\data\Persistence;
use atk4\login\Acl;
use atk4\login\Auth;
use atk4\ui\Layout;

/**
 * Example implementation of your Authenticated application.
 */
class App extends \atk4\ui\App
{
    use \atk4\core\ConfigTrait;

    public $db;
    public $auth;
    public $title = 'Auth Demo App';

    /**/

    public function __construct()
    {
        parent::__construct();

        $this->initLayout([Layout\Admin::class]);

        // Construct menu
        $this->layout->menuLeft->addItem(['Dashboard', 'icon' => 'info'], ['index']);
        $this->layout->menuLeft->addItem(['Setup demo database', 'icon' => 'cogs'], ['admin-setup']);

        $g = $this->layout->menuLeft->addGroup(['Forms']);
        $g->addItem(['Sign-up form', 'icon' => 'table'], ['form-register']);
        $g->addItem(['Login form', 'icon' => 'table'], ['form-login']);
        $g->addItem(['Forgot password form', 'icon' => 'table'], ['form-forgot']);

        $g = $this->layout->menuLeft->addGroup(['ACL']);
        $g->addItem(['User roles', 'icon' => 'id card'], ['acl-roles']);

        $g = $this->layout->menuLeft->addGroup(['Admin']);
        $g->addItem(['User Admin', 'icon' => 'users'], ['admin-users']);
        $g->addItem(['Role Admin', 'icon' => 'tasks'], ['admin-roles']);

        $this->initAuth(false);
    }

    protected function initAuth($check = true)
    {
        $this->auth = new Auth(['check' => $check]);
        $this->auth->setApp($this);

        // Strangely can not setmodel at this stage :(
        //$m = new \atk4\login\Model\User($this->db);
        //$this->auth->setModel($m);

        //$this->auth->setAcl(new Acl(), $this->db);
    }
}

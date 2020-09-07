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

    public function __construct($interface = 'front', $no_db_connect = false, $no_authenticate = false)
    {
        parent::__construct();

        $config_file = __DIR__ . '/../config.php';

        if (!file_exists($config_file)) {
            $this->redirect('wizard.php');
            $this->callExit();
        }

        $this->readConfig($config_file, 'php');

        if ($interface === 'admin') {
            $this->initLayout([Layout\Admin::class]);
            $this->layout->menuLeft->addItem(['User Admin', 'icon' => 'users'], ['admin-users']);
            $this->layout->menuLeft->addItem(['Role Admin', 'icon' => 'tasks'], ['admin-roles']);
            $this->layout->menuLeft->addItem(['Back to Demo Index', 'icon' => 'arrow left'], ['index']);
        } elseif ($interface === 'centered') {
            $this->initLayout([Layout\Centered::class]);
        } else {
            $this->initLayout([\atk4\login\Layout\Narrow::class]);
        }

        if (!$no_db_connect) {
            $this->db = Persistence::connect($this->config['dsn']);
            $this->db->app = $this;
        }

        if (!$no_authenticate) {
            $this->authenticate();
        }
    }

    public function authenticate()
    {
        $this->auth = new Auth(['check' => true]);
        $this->auth->app = $this;

        $m = new \atk4\login\Model\User($this->db);
        $this->auth->setModel($m);

        $this->auth->setAcl(new Acl(), $this->db);
    }
}

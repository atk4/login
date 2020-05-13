<?php

namespace atk4\login\demo;

/**
 * Example implementation of your Authenticated application.
 *
 * @package atk4\login\demo
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

        $this->readConfig($config_file, 'php-inline');

        if ($interface == 'admin') {
            $this->initLayout('Admin');
            $this->layout->menuLeft->addItem(['User Admin', 'icon'=>'users'], ['admin-users']);
            $this->layout->menuLeft->addItem(['Role Admin', 'icon'=>'tasks'], ['admin-roles']);
            $this->layout->menuLeft->addItem(['Back to Demo Index', 'icon'=>'arrow left'], ['index']);
        } elseif ($interface == 'centered') {
            $this->initLayout('Centered');
        } else {
            $this->initLayout(new \atk4\login\Layout\Narrow());
        }

        if (!$no_db_connect) {
            $this->dbConnect($this->config['dsn']);
        }

        if (!$no_authenticate) {
            $this->authenticate();
        }
    }

    public function authenticate()
    {
        $this->auth = $this->add(new \atk4\login\Auth(['check'=>true]));

        $m = new \atk4\login\Model\User($this->db);
        $this->auth->setModel($m);

        $this->auth->setACL(new \atk4\login\ACL(), $this->db);
    }
}

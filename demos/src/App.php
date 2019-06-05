<?php


namespace atk4\login\demo;


/**
 * Example implementation of your Authenticated application.
 *
 * @package atk4\login\demo
 */
class App extends \atk4\ui\App {
    public $db;
    public $auth;
    public $title = 'Auth Demo App';

    public $dsn = 'mysql://root:root@localhost/login';

    function __construct($interface = 'front', $no_db_connect = false) {

        parent::__construct();


        if ($interface == 'admin') {
            $this->initLayout('Admin');
            $this->layout->leftMenu->addItem(['Demo Index', 'icon'=>'gift'], ['index']);
            $this->layout->leftMenu->addItem(['Admin', 'icon'=>'users'], ['admin']);
        } elseif ($interface == 'centered') {
            $this->initLayout('Centered');
        } else {
            $this->initLayout(new \atk4\login\Layout\Narrow());
        }

        if (!$no_db_connect) {
            $this->dbConnect($this->dsn);
        }

        $this->authenticate();
    }

    function authenticate()
    {
        $this->auth = $this->add(new \atk4\login\Auth(['check'=>false]));
        $m = new \atk4\login\Model\User($this->db);
        $m->addCondition('is_admin', false);

        $this->auth->setModel(
            new \atk4\login\Model\User($this->db)
        );

        $this->auth->setACL(new TestACL(), $this->db);
    }
}
